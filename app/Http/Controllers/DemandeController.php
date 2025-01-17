<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Compte;
use App\Models\Demande;
use App\Mail\DemandeMail;
use App\Models\Livraison;
use App\Models\Delegation;
use App\Models\Traitement;
use App\Models\Approbateur;
use App\Mail\DeliveriesMail;
use Illuminate\Http\Request;
use App\Models\DemandeDetail;
use App\Models\Mail as MailModel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Direction;

class DemandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $connected_user = Session::get('authUser');

        if ($this->isManager($connected_user)) {
            $connected_user['manager'] = true;
        }

        if ($this->isApprover($connected_user)) {
            $connected_user['approver'] = true;
        }

        if ($this->isDelegated($connected_user)) {
            $connected_user['delegated'] = true;
        }

        if ($connected_user->compte->role->value === 'livraison') {
            $connected_user['deliver'] = true;
        }

        $ongoings = $this->getOngoingReqs($connected_user);
        $collaborators = $this->getCollaboratorsReqs($connected_user);
        $delegations = $this->getDelegationsReqs($connected_user);
        $validate = $this->getReqsToValidate($connected_user);
        $historics = $this->getReqsHistoric($connected_user);
        $statistics = $this->getStatistics($connected_user);
        return view('demandes.index', compact('ongoings', 'connected_user', 'historics', 'collaborators', 'delegations', 'validate', 'statistics'));
    }

    private function isManager($user)
    {
        if (User::whereHas('compte', function (Builder $query) use ($user) {
            $query->where('manager', $user->id)->where('user_id', '!=', $user->id);
        })->exists()) {
            return true;
        } else {
            return false;
        }
    }

    private function isApprover($user)
    {
        if (Approbateur::where('email', $user->email)->exists()) {
            return true;
        } else {
            return false;
        }
    }

    private function isDelegated($user)
    {
        if (Delegation::where('user_id', $user->id)->where('date_debut', '<=', Carbon::today())->where('date_fin', '>=', Carbon::today())->exists()) {
            return true;
        } else {
            return false;
        }
    }

    private function getValidationFlows($req)
    {
        $last_flow = Traitement::where('demande_id', $req->id)->orderBy('id', 'desc')->first();
        $flows_datas = [];
        if ($last_flow->status === 'en_cours') {
            $approvers = Approbateur::orderBy('level')->get();
            $firsts_flows = Traitement::where('demande_id', $req->id)->where('level', 0)->get();
            foreach ($firsts_flows as $first_flow) {
                $validator = User::find($first_flow->approbateur_id);
                $flows_datas[] = [
                    'validator' => $validator->name,
                    'status' => $first_flow->status,
                    'date' => $first_flow->updated_at->format('d-m-Y')
                ];
            }
            foreach ($approvers as $key => $approver) {
                $flow = Traitement::where('demande_id', $req->id)->where('level', $key + 1)->first();
                if ($flow) {
                    $validator = User::find($flow->approbateur_id);
                    $flows_datas[] = [
                        'validator' => $validator->name,
                        'status' => $flow->status,
                        'date' => $flow->updated_at->format('d-m-Y')
                    ];
                } else {
                    $validator = User::where('email', $approver->email)->first();
                    $flows_datas[] = [
                        'validator' => $validator->name,
                        'status' => '',
                        'date' => ''
                    ];
                }
            }
        } else {
            $flows = Traitement::where('demande_id', $req->id)->get();
            foreach ($flows as $flow) {
                $validator = User::find($flow->approbateur_id);
                $flows_datas[] = [
                    'validator' => $validator->name,
                    'status' => $flow->status,
                    'date' => $flow->updated_at->format('d-m-Y')
                ];
            }
        }
        return $flows_datas;
    }

    private function getOngoingReqs($user)
    {
        if ($user->compte->role->value === 'user') {
            $reqs = Demande::with('demande_details')->whereHas('traitement', function ($query) use ($user) {
                $query->where('demandeur_id', $user->id)->where('status', 'en_cours');
            })->latest()
                ->paginate(12);
            foreach ($reqs as $req) {
                $last_flow = Traitement::where('demande_id', $req->id)->orderBy('id', 'desc')->first();
                $req['level'] = $last_flow->level;
                if ($last_flow && $last_flow->approbateur_id === $user->id) {
                    if ($req->user_id === $user->id) {
                        $req['validator'] = true;
                    } else {
                        $req['validator'] = false;
                    }
                }
                $req['flows'] = $this->getValidationFlows($req);
            }
        }

        if ($user->compte->role->value === 'livraison') {
            $demandes = Demande::all();
            $all_validated_keys = [];
            foreach ($demandes as $key => $req) {
                $last = Traitement::where('demande_id', $req->id)->orderBy('id', 'DESC')->first();
                if ($last && $last->status === 'valide') {
                    $all_validated_keys[$key] = $req->id;
                }
            }

            $validated_reqs = Demande::whereIn('id', $all_validated_keys)->get();
            $on_going = [];
            foreach ($validated_reqs as $key => $validated) {
                $req_details = DemandeDetail::where('demande_id', $validated->id)->get();
                $delivered = 0;
                foreach ($req_details as $req_detail) {
                    $req_count = $req_detail->qte_demandee;
                    $count = 0;
                    if (Livraison::where('demande_detail_id', $req_detail->id)->exists()) {
                        $deliveries = Livraison::where('demande_detail_id', $req_detail->id)->get();
                        foreach ($deliveries as $key => $delivery) {
                            $count += $delivery->quantite;
                        }
                        if ($req_count === $count) {
                            $delivered += 1;
                        }
                    }
                }
                if ($delivered < $req_details->count()) {
                    $on_going[] = $validated;
                }
            }
            $demandes_array = collect($on_going);
            $reqs = Demande::with('demande_details')->whereIn('id', $demandes_array->pluck('id'))->orderBy('id', 'desc')->latest()->paginate(12);
        }
        return $reqs;
    }

    private function getCollaboratorsReqs($user)
    {
        $isManager = Compte::where('manager', $user->id)->exists();
        if ($isManager) {
            $userCollaborators = User::whereHas('compte', function (Builder $query) use ($user) {
                $query->where('manager', $user->id)->where('user_id', '!=', $user->id);
            })->get();
            $collabs_req_keys = [];
            foreach ($userCollaborators as $collaborator) {
                $collab_reqs = Demande::with('demande_details')->where('user_id', $collaborator->id)->latest()->get();
                foreach ($collab_reqs as $req) {
                    $last_flow = Traitement::where('demande_id', $req->id)->orderBy('id', 'desc')->first();
                    if ($last_flow && $last_flow->status === 'en_cours') {
                        if ($last_flow->approbateur_id === $user->id) {
                            $collabs_req_keys[] = $req->id;
                        }
                    }
                }
            }
            $demandes = Demande::with('demande_details')->whereIn('id', $collabs_req_keys)->latest()->paginate(9);
            foreach ($demandes as $demande) {
                $demande['flows'] = $this->getValidationFlows($demande);
                $demande['level'] = Traitement::where('demande_id', $req->id)->orderBy('id', 'desc')->first()->level;
                $demande['validator'] = true;
            }
        } else {
            $demandes = [];
        }
        return $demandes;
    }

    private function getDelegationsReqs($user)
    {
        $delegation = Delegation::where('user_id', $user->id)->where('date_debut', '<=', Carbon::today())->where('date_fin', '>=', Carbon::today())->first();
        if ($delegation) {
            $manager = User::find($delegation->delegant);
            if ($this->isManager($manager) || $this->isApprover($manager)) {
                $demandes = Demande::with('demande_details')->whereHas('traitement', function (Builder $query) use ($manager) {
                    $query->where('approbateur_id', $manager->id)->where('status', 'en_cours');
                })->latest()->get();

                foreach ($demandes as $demande) {
                    $demande['flows'] = $this->getValidationFlows($demande);
                    $last_flow = Traitement::where('demande_id', $demande->id)->where('approbateur_id', $manager->id)->get()->last();
                    $demande['level'] = $last_flow->level;
                    if ($last_flow->approbateur_id === $manager->id) {
                        $demande['validator'] = true;
                    } else {
                        $demande['validator'] = false;
                    }
                }
            }

            if ($manager->compte->role->value === 'livraison') {
                $user['deliver'] = true;
                $reqs = Demande::all();
                $all_validated_keys = [];
                foreach ($reqs as $key => $req) {
                    $last = Traitement::where('demande_id', $req->id)->orderBy('id', 'DESC')->first();
                    if ($last && $last->status === 'valide') {
                        $all_validated_keys[$key] = $req->id;
                    }
                }

                $validated_reqs = Demande::whereIn('id', $all_validated_keys)->get();
                $on_going = [];
                foreach ($validated_reqs as $key => $validated) {
                    $req_details = DemandeDetail::where('demande_id', $validated->id)->get();
                    $delivered = 0;
                    foreach ($req_details as $req_detail) {
                        $req_count = $req_detail->qte_demandee;
                        $count = 0;
                        if (Livraison::where('demande_detail_id', $req_detail->id)->exists()) {
                            $deliveries = Livraison::where('demande_detail_id', $req_detail->id)->get();
                            foreach ($deliveries as $key => $delivery) {
                                $count += $delivery->quantite;
                            }
                            if ($req_count === $count) {
                                $delivered += 1;
                            }
                        }
                    }
                    if ($delivered < $req_details->count()) {
                        $on_going[] = $validated;
                    }
                }
                $demandes_array = collect($on_going);
                $demandes = Demande::with('demande_details')->whereIn('id', $demandes_array->pluck('id'))->orderBy('id', 'desc')->get();
            }
        } else {
            $demandes = [];
        }
        return $demandes;
    }

    private function getReqsToValidate($user)
    {
        if ($user->approver) {
            $demandes = Demande::with('demande_details')->whereHas('traitement', function (Builder $query) use ($user) {
                $query->where('approbateur_id', $user->id)->where('status', 'en_cours');
            })->latest()->get();

            foreach ($demandes as $demande) {
                $demande['flows'] = $this->getValidationFlows($demande);
                $last_flow = Traitement::where('demande_id', $demande->id)->where('approbateur_id', $user->id)->get()->last();
                $demande['level'] = $last_flow->level;
                if ($last_flow->approbateur_id === $user->id) {
                    $demande['validator'] = true;
                } else {
                    $demande['validator'] = false;
                }
            }
        } else {
            $demandes = [];
        }
        return $demandes;
    }

    private function getStatistics($user)
    {
        $demandes = Demande::all();
        $stats = [];
        $all_validated_keys = [];

        //Toutes les requisitionqs validées
        foreach ($demandes as $key => $demande) {
            $last = Traitement::where('demande_id', $demande->id)->orderBy('id', 'DESC')->first();
            if ($last && $last->status === 'valide') {
                $all_validated_keys[$key] = $demande->id;
            }
        }
        $validated_reqs = Demande::whereIn('id', $all_validated_keys)->get();
        $reqs_delivered = [];
        $stats['all_reqs'] = $validated_reqs->count();

        // Les requisitionqs livrées
        foreach ($validated_reqs as $key => $validated) {
            $req_details = DemandeDetail::where('demande_id', $validated->id)->get();
            $delivered = 0;
            foreach ($req_details as $req_detail) {
                $req_count = $req_detail->qte_demandee;
                $deliveries = Livraison::where('demande_detail_id', $req_detail->id)->get();
                $count = 0;
                if ($deliveries->count() > 0) {
                    foreach ($deliveries as $key => $delivery) {
                        $count += $delivery->quantite;
                    }
                }
                if ($req_count === $count) {
                    $delivered += 1;
                }
            }
            if ($delivered === $req_details->count()) {
                $reqs_delivered[] = $validated;
            }
        }
        $demandes_array = collect($reqs_delivered);
        $stats['delivered'] = Demande::whereIn('id', $demandes_array->pluck('id'))->orderBy('created_at', 'desc')->count();


        // Les requisitionqs du mois
        $validated_keys = [];
        foreach ($validated_reqs as $key => $validated) {
            $validated_keys[$key] = $validated->id;
        }
        $req_month_count = Demande::whereIn('id', $validated_keys)->whereMonth('created_at', Carbon::now()->month)->count();
        $stats['month_count'] = $req_month_count;
        Carbon::setlocale('fr');
        $months = [];
        for ($month = 0; $month <= 11; $month++) {
            $months[$month]['name'] = Carbon::create()->month($month + 1)->translatedFormat('F');
            $months[$month]['count'] = Demande::whereIn('id', $validated_keys)->whereMonth('created_at', $month + 1)->whereYear('created_at', Carbon::now()->year)->count();
        }

        // les requisitionqs par Directions
        $directions = Direction::withTrashed()->get();
        $array_directions_req_count = [];
        
        foreach ($directions as $direction) {
            $userD = User::whereHas('compte', function (Builder $query) use ($direction) {
                $query->where('direction_id', $direction->id);
            })->get();
            if ($userD) {
                $users_count = 0;
                foreach ($userD as $user) {
                    $user_reqs = Demande::where('user_id', $user->id)->get();
                    $user_req_validated = [];
                    $user_count = 0;
                    if ($user_reqs !== []) {
                        foreach ($user_reqs as $user_req) {
                            $last_tr = Traitement::where('demande_id', $user_req->id)->orderBy('id', 'desc')->first();
                            if ($last_tr && $last_tr->status === 'valide') {
                                $user_req_validated[] = $user_req;
                            }
                        }
                        $user_count = count($user_req_validated);
                    }
                    $users_count += $user_count;
                }
            }
            $direction['req_count'] = $users_count;
            $array_directions_req_count[] = $users_count;
        }
        // dd($directions);
        $best_direction = $directions->where('req_count', max($array_directions_req_count))->first();
        $stats['directions'] = $directions;
        $stats['best_direction'] = $best_direction;
        $stats['months'] = $months;
        return $stats;
    }

    private function getReqsHistoric($user)
    {
        if ($user->compte->role->value === 'user') {
            $demandes = Demande::with('demande_details')->whereHas('traitement', function (Builder $query) use ($user) {
                $query->where('demandeur_id', $user->id)
                    ->where('status', '!=', 'en_cours');
            })
                ->orderBy('created_at', 'desc')
                ->paginate(9);
        }
        if ($user->compte->role->value === 'livraison') {
            $reqs = Demande::all();
            $all_validated_keys = [];
            foreach ($reqs as $key => $req) {
                $last = Traitement::where('demande_id', $req->id)->orderBy('id', 'DESC')->first();
                if ($last && $last->status === 'valide') {
                    $all_validated_keys[$key] = $req->id;
                }
            }
            $demandes = Demande::with('demande_details')->whereIn('id', $all_validated_keys)->latest()->paginate(9);
        }

        foreach ($demandes as $key => $req) {
            $req['flows'] = $this->getValidationFlows($req);
            $last_flow = Traitement::where('demande_id', $req->id)->orderBy('id', 'DESC')->first();
            if ($last_flow->status === 'valide') {
                $req['validated'] = true;
                $details = $req->demande_details()->get();
                $count = 0;
                $partial = 0;
                foreach ($details as $key => $detail) {
                    if ($detail->qte_demandee === $detail->qte_livree) {
                        $count += 1;
                    } elseif ($detail->qte_livree > 0) {
                        $partial += 1;
                    }
                }
                if ($count === $details->count()) {
                    $req['delivered'] = true;
                    $req['status'] = 'Livrée';
                } elseif ($partial > 0) {
                    $req['status'] = 'Partiellement livrée';
                } else {
                    $req['validated'] = true;
                    $req['status'] = 'En attente de livraison';
                }
            } elseif ($last_flow->status === 'rejete') {
                $req['validated'] = true;
                $req['status'] = 'Rejeté';
            } else {
                $req['status'] = 'En cours';
            }
        }
        // dd($demandes);
        return $demandes;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $order = Demande::count() === 0 ? 1 : Demande::get()->last()->id + 1;
        $ref = "REQ-{$order}-" . Carbon::now()->year;
        $demande = Demande::create([
            'numero' => $ref,
            'service' => Session::get('authUser')->compte->service,
            'user_id' => Session::get('authUser')->id
        ]);
        if ($demande) {
            foreach ($request->demandes as $item) {
                DemandeDetail::create([
                    'designation' => $item["designation"],
                    'qte_demandee' => $item["qte_demandee"],
                    'qte_livree' => 0,
                    'demande_id' => $demande->id
                ]);
            }

            $traitement1 = Traitement::create([
                'demande_id' => $demande->id,
                'approbateur_id' => $demande->user->id,
                'demandeur_id' => $demande->user->id,
                'status' => 'valide',
            ]);

            if ($traitement1) {

                $traitement2 = Traitement::create([
                    'demande_id' => $demande->id,
                    'approbateur_id' => $demande->user->compte->manager,
                    'demandeur_id' => $demande->user->id,
                ]);
                if ($traitement2) {
                    MailModel::create([
                        'traitement_id' => $traitement2->id,
                    ]);

                    $demande['success'] = true;

                    Mail::to($demande->user->email, $demande->user->name)->send(new DemandeMail($demande));
                }
            }

            return redirect()->route('demandes.index')->with('success', 'Demande enregistrée avec succès');
        }

        return redirect()->route('demandes.index')->with('success', 'Demande enregistrée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Demande $demande)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Demande $demande)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Demande $demande)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demande $demande)
    {
        $demande->delete();
        return redirect()->back()->with('success', 'Suppression éffectuée avec succès');
    }

    public function updateLivraison(Request $request)
    {
        $request->validate([
            'details' => 'required|array',
        ]);

        $req = Demande::find(DemandeDetail::find((int)$request->details[0]["id"])->demande_id);
        $details = $request->details;
        $deliveries = [];
        foreach ($details as $detail) {
            if ($detail['quantite'] !== null) {
                $demandeDetail = DemandeDetail::find((int)$detail['id']);
                $new_quantity = $demandeDetail->qte_livree + $detail['quantite'];
                if ($new_quantity > $demandeDetail->qte_demandee) {
                    return redirect()->back()->with('error', 'La quantité livrée ne peut pas être supérieure à la quantité demandée.');
                } else {
                    $delivery = Livraison::create([
                        'demande_detail_id' => $detail['id'],
                        'quantite' => $detail['quantite']
                    ]);

                    if ($delivery) {
                        $deliveries[] = [
                            'to_deliver' => $demandeDetail->qte_demandee,
                            'designation' => $demandeDetail->designation,
                            'delivered' => $detail['quantite']
                        ];
                        $demandeDetail->update([
                            'qte_livree' => $new_quantity
                        ]);
                    }
                }
            }
        }
        $req['deliveries'] = $deliveries;
        Mail::to($req->user->email, $req->user->name)->send(new DeliveriesMail($req));

        return redirect()->back()->with('success', 'Livraison mise à jour avec succès');
    }
}
