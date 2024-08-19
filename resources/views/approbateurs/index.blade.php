<x-app-layout>
    <div>
        @if (session()->has('message'))
            <div id="alert-3"
                class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">
                    {{ session()->get('message') }};
                    <button type="button"
                        class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700"
                        data-dismiss-target="#alert-3" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>
        @endif
        @if (session()->has('error'))
            <div id="alert-3"
                class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ms-3 text-sm font-medium">
                    {{ session()->get('error') }}
                    <button type="button"
                        class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700"
                        data-dismiss-target="#alert-3" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>
        @endif
    </div>
    <div class="relative flex flex-col min-w-0 break-words w-full mb-6 shadow-lg rounded ">
        <div class="rounded-t mb-0 px-4 py-3 border-0">
            <div class="flex flex-wrap items-center">
                <div class="relative w-full px-4 max-w-full flex-grow flex-1">
                    <h3 class=" font-bold text-base dark:text-white">Approvers</h3>
                </div>
                <div class="relative w-full px-4 max-w-full flex-grow flex-1 text-right">
                    <div class="flex justify-end my-2 space-x-1">
                        <button id="add">
                        <i class="material-icons-outlined text-green-400 font-bold text-xl">add</i>
                        </button>
                        <a id="enableAllBtn" onclick="editAction()"
                            class=" px-2 py-2  text-gray-400 hover:text-gray-100  mx-2">
                            <i class="material-icons-outlined text-base">edit</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="block w-full overflow-x-auto mt-3 dark:bg-gray-800 rounded-xl">
            <form action="{{ route('approbateurs.store') }}" method="post">
                @csrf
                <table class="table w-full text-gray-400 border-separate space-y-6 text-sm">
                    <thead class="dark:bg-gray-800 text-gray-500">
                        <tr class="text-black dark:text-white">
                            <th class="p-3 text-left"></th>
                            <th class="p-3 text-left">Names</th>
                            <th class="p-3 text-left">Functions</th>
                            <th class="p-3 text-left">Emails</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        @foreach ($approbateurs as $approbateur)
                            <tr data-id="{{ $approbateur->id }}" class="dark:bg-gray-800">
                                <td class="p-3">
                                    <input type="number" value="{{ $approbateur->level }}" name="level[]"
                                        class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-24 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        style="display: none;"disabled>
                                </td>
                                <td class="p-3">
                                    <input type="text" name="name[]" value="{{ $approbateur->name }} "
                                        class="in name_approver bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        disabled>
                                </td>
                                <td class="p-3 font-bold">
                                    <input type="text" name="fonction[]" value="{{ $approbateur->fonction }}"
                                        class="in  bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        disabled>
                                </td>
                                <td class="p-3">
                                    <input type="text" name="email[]" value="{{ $approbateur->email }}"
                                        class="in bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        disabled>
                                </td>
                                <td class="p-3 ">
                                    <a href="#" onclick="supprimer(event);" data-modal-target="delete-modal"
                                        data-modal-toggle="delete-modal"
                                        href="{{ route('approbateurs.destroy', $approbateur->id) }}"
                                        data-id="{{ $approbateur->id }}">
                                        <i class="material-icons-round text-base text-red-500">delete_outline</i>
                                    </a>
                                </td>
                                <td>
                                    <input type="hidden" name="id[]" value="{{ $approbateur->id }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class=" flex justify-end mx-14 py-3 space-x-2">
                    <button id="saveBtn" type="submit" class=" px-2 py-1 bg-green-400 text-white rounded" style="display: none;">Save</button>
                    <button id="create" type="submit" class=" px-2 py-1 bg-green-400 text-white rounded" style="display: none;">Create</button>
                </div>
            </form>
        </div>
    </div>
    <div>
        <x-deleteApprobateur/>
    </div>
    <script src="https:ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script>
        
        $(document).ready(function(){
            $("#sortable").sortable({
                helper:function(e, row){
                    var originalCells = row.children();
                    var cloneRow = row.clone();
                    cloneRow.children().each(function(index){
                        $(this).width($(originalCells[index]).width());
                    });
                    return cloneRow;
                },
                update: function(event, ui) {
                    var approbateurIds = [];
                    $('#sortable tr').each(function() {
                        approbateurIds.push($(this).data('id'));
                    });
                    $.ajax({
                        url: '{{route('update.levels')}}',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            approbateurIds: approbateurIds,
                        },
                        success: function(response) {
                            if (response.data) {
                                console.log("Les niveaux ont été mis à jour avec succès");
                                
                    }},

                        error: function(xhr, status, error) {
                            console.error('Erreur AJAX : ' + error);}
                        
                });
                }
            });
        });
        function editAction() {
            var inputs = document.querySelectorAll('.in');
            inputs.forEach(function(input) {
                input.removeAttribute('disabled');
            });
            var a = document.getElementById('sortable');
            var b = a.rows.length
            if (b == 0) {
                document.getElementById('saveBtn').style.display = 'none';
            }else{
                document.getElementById('saveBtn').style.display = 'block';
                document.getElementById('create').style.display = 'none';   
                $('tr.new-row').remove();// supprimer les lignes ajoutées si elles ne sont pas valider
            }
        }
        $(document).ready(function() {
            $("#add").click(function() {
                var a = document.getElementById('sortable');
                var b = a.rows.length;
                var i = b+1;
                if(b == 4){
                    document.getElementById('add').style.display = 'none';
                }else{
                document.getElementById('create').style.display = 'block';
                document.getElementById('saveBtn').style.display = 'none';
                var tr = `

                    <tr class="new-row ui-widget bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="p-3">
                            <input type="hidden" name="level[]" value="${i}"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        </td>
						<td class="p-3">
                            <input type="text" name="name[]" class="name-approver bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        </td>
						<td class="p-3 font-bold">
						    <input type="text" name="fonction[]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        </td>
						<td class="p-3">
                            <input type="email" name="email[]" class="cursor-not-allowed email-approver bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required >
                        </td>
						<td class="p-3 ">
                            <button type="button" class="delete ">
                                <i class="material-icons-outlined text-green-400 font-bold text-xl">cancel</i>
                            </button>
						</td>
					</tr>
                    
                `;
                        $("tbody").append(tr);
                    }
                });
                $('tbody').on('click', '.delete', function() {
                    $(this).closest('tr').remove();
                    var a = document.getElementById('sortable');
                    var b = a.rows.length;
                    var i = b + 1;
                    if (b == 4 && $a == true) {
                        document.getElementById('add').style.display = 'none';
                    } else {
                        document.getElementById('add').style.display = 'block';
                        document.getElementById('create').style.display = 'none';
                    }
                });
                // autocomplete avec l'API 
                fetch('http://10.143.41.70:8000/promo2/odcapi/?method=getUsers')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            var autocompleteData = data.users.reduce((acc, user) => {
                                acc[`${user.first_name} ${user.last_name}`] = user.email;
                                return acc;
                            }, {});
                            initAutocomplete(autocompleteData);
                        } else {
                            console.error('Erreur lors de la récupération des données :', data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des données :', error);
                    });

                function initAutocomplete(data) {
                    $('tbody').on('focus', '.name-approver, .email-approver', function() {
                        var currentInput = $(this);
                        currentInput.autocomplete({
                            source: function(request, response) {
                                var term = request.term.toLowerCase();
                                var filteredItems = Object.keys(data).filter(function(key) {
                                        return key.toLowerCase().indexOf(term) !== -1 || data[
                                            key].toLowerCase().indexOf(term) !== -1;
                                    })
                                    .slice(0, 10) //limiter à 10
                                ;
                                response(filteredItems);
                            },
                            select: function(event, ui) {
                                var selectedValue = ui.item.value;
                                currentInput.val(selectedValue);
                                if (currentInput.hasClass('name-approver')) {
                                    var selectedEmail = data[selectedValue];
                                    currentInput.closest('tr').find('.email-approver').val(
                                        selectedEmail);
                                }
                            }
                        });
                    });
                }
            })
            // update avec ajax
            $('#saveBtn').click(function() {
                $('#add').show();
                $('input').prop('disabled', true);
                $('#saveBtn').hide("drop", {
                    direction: "down"
                }, "slow");
                var approbateurs = [];
                $('tbody tr').each(function() {
                    var id = $(this).data('id');
                    var level = $(this).find('input[name="level[]"]').val();
                    var name = $(this).find('input[name="name[]"]').val();
                    var fonction = $(this).find('input[name="fonction[]"]').val();
                    var email = $(this).find('input[name="email[]"]').val();
                    approbateurs.push({
                        id: id,
                        level: level,
                        name: name,
                        fonction: fonction,
                        email: email
                    });
                });
                $.ajax({
                    url: "approbateurs/update",
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        approbateurs: approbateurs
                    }),
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        console.log('Une erreur s\'est produite lors de la sauvegarde des modifications');
                    }
                });
            });
        </script>

</x-app-layout>
