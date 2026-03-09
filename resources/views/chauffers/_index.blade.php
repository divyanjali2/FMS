{{-- WHOLE CHAUFFERS SECTION AS ONE ACCORDION --}}
<div class="accordion mb-4" id="chauffersSectionAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="chauffersSectionHeading">
            <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#chauffersSectionCollapse"
                    aria-expanded="false"
                    aria-controls="chauffersSectionCollapse">
                <span class="fw-bold">Chauffers</span>
            </button>
        </h2>

        <div id="chauffersSectionCollapse"
             class="accordion-collapse collapse"
             aria-labelledby="chauffersSectionHeading"
             data-bs-parent="#chauffersSectionAccordion">

            <div class="accordion-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0 fw-bold">Chauffers</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChaufferModal">
                        + Add Chauffer
                    </button>
                </div>

                {{-- List (simple table) --}}
                <div class="card">
                    <div class="card-body">
                        @if($chauffers->count() === 0)
                            <p class="mb-0">No chauffers yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($chauffers as $i => $chauffer)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $chauffer->name }}</td>
                                                <td>{{ $chauffer->phone_number }}</td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editChaufferModal" data-id="{{ $chauffer->id }}" data-name="{{ $chauffer->name }}" data-phone="{{ $chauffer->phone_number }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>


                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteChaufferModal" data-id="{{ $chauffer->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div> {{-- accordion-body --}}
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addChaufferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('chauffers.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Chauffer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" type="text" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone_number" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editChaufferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" id="editChaufferForm">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Chauffer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" type="text" name="name" id="edit_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone_number" id="edit_phone" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-warning" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // EDIT
        const editModal = document.getElementById('editChaufferModal');
        const editForm  = document.getElementById('editChaufferForm');

        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id    = button.getAttribute('data-id');
            const name  = button.getAttribute('data-name');
            const phone = button.getAttribute('data-phone');

            editForm.action = `{{ url('/chauffers') }}/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_phone').value = phone;
        });

        // DELETE
        const deleteModal = document.getElementById('deleteChaufferModal');
        const deleteForm  = document.getElementById('deleteChaufferForm');
        const noteField   = document.getElementById('chauffer_delete_note');

        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            deleteForm.action = `{{ url('/chauffers') }}/${id}`; 
            noteField.value = '';
        });

        // Fix delete action build (avoid template confusion)
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            deleteForm.action = `{{ url('/chauffers') }}/${id}`;
        });

        deleteForm.addEventListener('submit', function (e) {
            if (!noteField.value.trim()) {
                e.preventDefault();
                alert('Please provide a note for deletion.');
                noteField.focus();
            }
        });

    });
</script>