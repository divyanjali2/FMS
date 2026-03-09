<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                 style="width: 60px; height: 60px; font-size: 1.5rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="ms-3">
                <h5 class="card-title mb-1">{{ $user->name }}</h5>
                <span class="badge bg-success">Active</span>
            </div>
        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-envelope me-2 text-primary"></i> Email</span>
                <span class="fw-bold">{{ $user->email }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3 me-2 text-primary"></i> Created At</span>
                <span class="fw-bold">{{ $user->created_at->format('d M, Y') }}</span>
            </li>
        </ul>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
        <i class="bi bi-x-circle"></i> Close
    </button>
</div>
