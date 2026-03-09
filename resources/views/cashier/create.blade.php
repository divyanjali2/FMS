<div class="modal fade" id="addRentalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add Rental Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('cashier.store') }}" method="POST" id="rentalForm">
                    @csrf
                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Select Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    data-owner="{{ $vehicle->owner_name }}"
                                    data-bank="{{ $vehicle->cashier->bank_name ?? '' }}"
                                    data-account-number="{{ $vehicle->cashier->account_number ?? '' }}"
                                    data-account-name="{{ $vehicle->cashier->account_name ?? '' }}">
                                    {{ $vehicle->reg_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="owner_name" class="form-label">Owner Name</label>
                        <input type="text" id="owner_name" class="form-control" readonly>
                    </div>

                    <hr>

                    <div class="d-flex row">
                        <div class="col-md-6 mb-3">
                            <label for="rental_agreement_start_date" class="form-label">Rental Agreement Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="rental_agreement_start_date" id="rental_agreement_start_date" class="form-control" required>
                        </div>
    
                        <div class="col-md-6 mb-3">
                            <label for="rental_agreement_end_date" class="form-label">Rental Agreement End Date <span class="text-danger">*</span></label>
                            <input type="date" name="rental_agreement_end_date" id="rental_agreement_end_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="my-3">
                        <label for="due_day" class="form-label">Due Day of Month <span class="text-danger">*</span></label>
                        <select name="due_day" id="due_day" class="form-select" required>
                            <option value="">Select Day</option>
                            @for($i=1; $i<=30; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Bank <span class="text-danger">*</span></label>
                        <select name="bank_name" id="bank_name" class="form-select select2" required>
                            <option value="">Select Bank</option>
                        </select>
                        <div id="bank-warning" class="form-text text-danger d-none">
                            ⚠️ No bank details found for this vehicle. Please enter them manually.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" id="account_number" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name" id="account_name" class="form-control" required>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Rental</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#addRentalModal').on('shown.bs.modal', function () {
            $('#vehicle_id').select2({
                width: '100%',
                dropdownParent: $('#addRentalModal')
            });
        });

        $('#vehicle_id').on('change', function () {
            let selected = $(this).find(':selected');
            let ownerName = selected.data('owner') || '';
            let bankName = selected.data('bank') || '';
            let accountNumber = selected.data('account-number') || '';
            let accountName = selected.data('account-name') || '';

            $('#owner_name').val(ownerName);

            if (bankName) {
                $('#bank_name').val(bankName).trigger('change');
                $('#account_number').val(accountNumber);
                $('#account_name').val(accountName);
                $('#bank-warning').addClass('d-none'); 
            } else {
                $('#bank_name').val('').trigger('change');
                $('#account_number').val('');
                $('#account_name').val('');
                $('#bank-warning').removeClass('d-none');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bankSelect = document.getElementById('bank_name');

        const banks = [
            "Bank of Ceylon (BOC)",
            "Cargills Bank Ltd",
            "Citibank N.A.",
            "Commercial Bank of Ceylon PLC",
            "Commercial Credit and Finance",
            "DFCC Bank PLC",
            "HDFC Bank",
            "Hatton National Bank PLC (HNB)",
            "Hongkong and Shanghai Banking Corporation (HSBC)",
            "National Development Bank (NDB)",
            "National Savings Bank (NSB)",
            "Nations Trust Bank (NTB)",
            "Pan Asia Bank PLC",
            "People’s Bank",
            "Sanasa Development Bank",
            "Sampath Bank PLC",
            "Seylan Bank PLC",
            "Singer Finance",
            "Softlogic Finance",
            "Standard Chartered Bank",
            "State Mortgage and Investment Bank (SMIB)",
            "Union Bank of Colombo PLC",
            "Vallibel Finance",
            "Regional Development Bank (Pradeshiya Sanwardhana Bank)"
        ];

        // Populate the dropdown
        banks.forEach(bank => {
            const option = document.createElement('option');
            option.value = bank;
            option.textContent = bank;
            bankSelect.appendChild(option);
        });

        $('#bank_name').select2({
            width: '100%',
            dropdownParent: $('#addRentalModal'),
            placeholder: "Select Bank"
        });
    });
</script>

