@props(['details'])
<!-- Main modal -->
@if ($errors->any())
                        <div class="bg-red-500 text-white px-3 py-2 rounded-lg mb-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
<form action="{{ route('demandes.updateLivraison') }}" method="POST">
    @csrf

    <div id="default-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Mise à jour de la livraison
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="default-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->


                <div class="p-4 md:p-5 space-y-4">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">

                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>

                                    <th scope ="col" class="px-6 py-3">
                                        Désignation
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Quantité demandée
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Quantitée livrée
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">

                                @foreach ($details as $key => $detail)
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4">
                                            {{ $detail->designation }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $detail->qte_demandee }}
                                        </td>
                                        <td class="px-6 py-4">


                                            <div>

                                                <input type="hidden" name="details[{{ $key }}][id]"
                                                    value="{{ $detail->id }}" id="details[{{ $key }}][id]"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Ex.10" required />
                                            </div>



                                            <div class="flex justify-between gap-3">
                                                <x-text-input id="designation"
                                                    class="bg-gray-50 w-[80%] border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-green-500 dark:focus:border-green-500"
                                                    type="text" name="details[{{ $key }}][quantite]"
                                                    :value="old('quantite')" placeholder="Ex. 12" required autofocus
                                                    autocomplete="quantite" />
                                                <x-input-error :messages="$errors->get('quantite')" class="mt-2" />


                                            </div>
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>

                        </table>
                    </div>

                    {{-- </div> --}}



                    <!-- Modal footer -->
                    <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button data-modal-hide="default-modal" type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            style="margin-left: auto;">Mettre à jour</button>

                    </div>
                </div>
            </div>
        </div>
</form>
