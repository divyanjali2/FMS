@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Process Payment for {{ $cashier->vehicle->reg_no ?? '-' }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('fleet-decisions.storePayment', $cashier->id) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Cashier Amount</label>
                    <input type="text" class="form-control" value="{{ number_format($cashierAmount, 2) }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select PO Group</label>
                    <select id="poSelect" class="form-select" multiple required>
                        @foreach($procurementsByInspection as $inspectionId => $group)
                            @php
                                $poNumbers = $group->pluck('po_id')->implode('-');
                                $totalPrice = $group->sum('price');
                                $firstProcId = $group->first()->id;
                            @endphp
                            <option value="{{ $firstProcId }}" data-price="{{ $totalPrice }}">
                                Inspection #{{ $inspectionId }}: PO#{{ $poNumbers }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold CTRL (Windows) or CMD (Mac) to select multiple POs</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Selected PO Amount</label>
                    <input type="text" id="selectedPOAmount" class="form-control" value="0.00" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remaining Amount</label>
                    <input type="text" id="remainingAmount" class="form-control" value="{{ number_format($cashierAmount, 2) }}" readonly>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="installmentCheck">
                    <label class="form-check-label" for="installmentCheck">Split into Installments</label>
                </div>

                <div id="installmentOptions" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <div class="mb-3">
                        <label class="form-label">Installment Type</label>
                        <select id="installmentType" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="equal">Equal Installments</option>
                            <option value="custom">Custom Installments</option>
                        </select>
                    </div>

                    <div id="equalInstallmentDiv" class="mb-3" style="display: none;">
                        <label class="form-label">Number of Installments</label>
                        <input type="number" id="equalInstallmentCount" class="form-control" min="1" value="1">
                        <small class="text-muted">Amount per installment will be calculated automatically.</small>
                        <div class="mt-2">
                            <label class="form-label">Amount per Installment</label>
                            <input type="text" id="equalInstallmentAmount" class="form-control" readonly>
                        </div>
                    </div>

                    <div id="customInstallmentDiv" style="display: none;">
                        <label class="form-label">Custom Installments</label>
                        <textarea id="customInstallments" class="form-control" rows="3" placeholder="Enter amounts separated by comma (e.g. 1000, 1500, 2000)"></textarea>
                        <small class="text-muted">Total must equal the remaining amount.</small>
                    </div>
                </div>

                <input type="hidden" name="procurement_ids" id="procurementId">
                <input type="hidden" name="remaining_amount" id="hiddenRemainingAmount" value="{{ $cashierAmount }}">
                <input type="hidden" name="installment_check" id="installmentCheckInput" value="0">
                <input type="hidden" name="installment_type" id="installmentTypeInput" value="">
                <input type="hidden" name="equal_count" id="equalCountInput" value="">
                <input type="hidden" name="equal_amount" id="equalAmountInput" value="">
                <input type="hidden" name="custom_installments" id="customInstallmentsInput" value="">

                <div class="d-flex justify-content-end">
                    <a href="{{ route('fleet-decisions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('poSelect');
        const selectedPOField = document.getElementById('selectedPOAmount');
        const remainingField = document.getElementById('remainingAmount');
        const hiddenInput = document.getElementById('procurementId');
        const hiddenRemaining = document.getElementById('hiddenRemainingAmount');
        const cashierAmount = parseFloat({{ $cashierAmount }});

        const installmentCheck = document.getElementById('installmentCheck');
        const installmentOptions = document.getElementById('installmentOptions');
        const installmentType = document.getElementById('installmentType');
        const equalDiv = document.getElementById('equalInstallmentDiv');
        const customDiv = document.getElementById('customInstallmentDiv');
        const equalCount = document.getElementById('equalInstallmentCount');
        const equalAmount = document.getElementById('equalInstallmentAmount');
        const customTextarea = document.getElementById('customInstallments');

        const installmentCheckInput = document.getElementById('installmentCheckInput');
        const installmentTypeInput = document.getElementById('installmentTypeInput');
        const equalCountInput = document.getElementById('equalCountInput');
        const equalAmountInput = document.getElementById('equalAmountInput');
        const customInstallmentsInput = document.getElementById('customInstallmentsInput');

        // Show/hide installment options
        installmentCheck.addEventListener('change', function() {
            installmentOptions.style.display = this.checked ? 'block' : 'none';
            installmentCheckInput.value = this.checked ? '1' : '0';

            if (!this.checked) {
                installmentType.value = '';
                equalDiv.style.display = 'none';
                customDiv.style.display = 'none';
                installmentTypeInput.value = '';
                equalCountInput.value = '';
                equalAmountInput.value = '';
                customInstallmentsInput.value = '';
            }
        });

        // Show correct installment type divs
        installmentType.addEventListener('change', function() {
            equalDiv.style.display = this.value === 'equal' ? 'block' : 'none';
            customDiv.style.display = this.value === 'custom' ? 'block' : 'none';
            installmentTypeInput.value = this.value;

            if (this.value === 'equal') calculateEqualInstallment();
        });

        // Calculate equal installment
        equalCount.addEventListener('input', calculateEqualInstallment);

        function calculateEqualInstallment() {
            const count = parseInt(equalCount.value) || 1;
            const total = parseFloat(remainingField.value) || 0;
            const amount = total / count;
            equalAmount.value = amount.toFixed(2);

            equalCountInput.value = count;
            equalAmountInput.value = amount.toFixed(2);
        }

        // Sync custom installments textarea to hidden input
        customTextarea.addEventListener('input', function() {
            customInstallmentsInput.value = this.value;
        });

        // Update amounts when PO changes
        select.addEventListener('change', function() {
            let totalPO = 0;
            let procIds = [];

            // Loop through selected options
            Array.from(select.selectedOptions).forEach(option => {
                const poAmount = parseFloat(option.getAttribute('data-price') || 0);
                totalPO += poAmount;
                procIds.push(option.value);
            });

            selectedPOField.value = totalPO.toFixed(2);
            const remaining = cashierAmount - totalPO;
            remainingField.value = remaining.toFixed(2);
            hiddenRemaining.value = remaining.toFixed(2);
            hiddenInput.value = procIds.join(','); 

            // Update equal installment automatically if selected
            if (installmentType.value === 'equal') calculateEqualInstallment();
        });

        // Validate custom installments before form submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (installmentCheck.checked && installmentType.value === 'custom') {
                const remainingAmount = parseFloat(remainingField.value);
                const enteredAmounts = customTextarea.value
                    .split(',')
                    .map(v => parseFloat(v.trim()))
                    .filter(v => !isNaN(v));

                const total = enteredAmounts.reduce((sum, val) => sum + val, 0);

                if (total.toFixed(2) != remainingAmount.toFixed(2)) {
                    e.preventDefault();
                    alert('The sum of custom installments (' + total.toFixed(2) + 
                        ') does not equal the remaining amount (' + remainingAmount.toFixed(2) + ').');
                    customTextarea.focus();
                }
            }
        });
    });
</script>

@endsection
