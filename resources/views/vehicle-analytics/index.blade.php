@extends('layouts.app')

@section('content')
<div class="container py-2">
    <div class="card shadow-sm border-0 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 fw-bold">Vehicle Analytics</h3>
            <small class="text-muted">Search, sort, and export by period</small>
        </div>

        <hr class="mb-4 mt-0">

        <table class="table table-sm table-striped align-middle w-100" id="vehicleAnalyticsTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Vehicle No</th>
                    <th>Company</th>
                    <th>Used Days</th>
                    <th>Day Utilization %</th>
                </tr>
            </thead>

            <tbody>
                @foreach($rentals as $rental)
                    <tr
                        data-booking_no="{{ $rental->booking_number ?? '' }}"
                        data-vehicle="{{ $rental->vehicle->reg_no ?? '' }}"
                        data-company="{{ $rental->vehicle->company->name ?? '' }}"
                        data-arrival="{{ $rental->arrival_date ?? '' }}"
                        data-departure="{{ $rental->departure_date ?? '' }}"
                        data-passengers="{{ $rental->passengers ?? '' }}"
                        data-status="{{ $rental->status ?? '' }}"
                        data-repair_type="{{ $rental->repair_type ?? '' }}"
                    >
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $rental->vehicle->reg_no ?? '-' }}</td>
                        <td>{{ $rental->vehicle->company->name ?? '-' }}</td>
                        <td class="used-days-cell">-</td>
                        <td class="day-utilization-cell">-</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    window.allVehicles = @json(
        $vehicles->map(function ($v) {
            return [
                'vehicle' => $v->reg_no ?? '',
                'company' => $v->company->name ?? '',
            ];
        })->values()
    );
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>

<script>
$(function () {
    const $t = $('#vehicleAnalyticsTable');

    const table = $t.DataTable({
        pageLength: 10,
        responsive: true,
        order: [[3, 'desc']],
        language: {
            lengthMenu: "Show _MENU_ rows",
            info: "Showing _START_ to _END_ of _TOTAL_ bookings",
            paginate: { previous: "Prev", next: "Next" }
        }
    });

    const months = [
        ['01', 'Jan'],
        ['02', 'Feb'],
        ['03', 'Mar'],
        ['04', 'Apr'],
        ['05', 'May'],
        ['06', 'Jun'],
        ['07', 'Jul'],
        ['08', 'Aug'],
        ['09', 'Sep'],
        ['10', 'Oct'],
        ['11', 'Nov'],
        ['12', 'Dec']
    ];

    const monthOpts = months.map(([v, l]) => `<option value="${v}">${l}</option>`).join('');
    const years = collectYears();
    const yearOpts = years.map(y => `<option value="${y}">${y}</option>`).join('');

    const now = new Date();
    const currentYear = String(now.getFullYear());
    const currentMonth = String(now.getMonth() + 1).padStart(2, '0');

    $('#vehicleAnalyticsTable_filter')
        .addClass('d-flex align-items-center flex-wrap gap-2')
        .append(`
            <div class="d-flex align-items-center gap-2 ms-3 flex-wrap">
                <label class="mb-0 text-muted small">Period:</label>

                <select id="exportPeriod" class="form-select form-select-sm" style="width:110px;">
                    <option value="">Select...</option>
                    <option value="yearly">Yearly</option>
                    <option value="monthly">Monthly</option>
                </select>

                <select id="exportYear" class="form-select form-select-sm" style="width:100px; display:none;">
                    <option value="">Year</option>
                    ${yearOpts}
                </select>

                <select id="exportMonth" class="form-select form-select-sm" style="width:100px; display:none;">
                    <option value="">Month</option>
                    ${monthOpts}
                </select>

                <button id="exportExcelBtn" class="btn btn-sm btn-primary" style="display:none;">Export Excel</button>
            </div>
        `);

    $('#exportPeriod').val('monthly');
    $('#exportYear').val(currentYear).show();
    $('#exportMonth').val(currentMonth).show();
    $('#exportExcelBtn').show();

    $(document).on('change', '#exportPeriod', function () {
        const period = this.value;

        $('#exportYear').toggle(!!period);
        $('#exportMonth').toggle(period === 'monthly');
        $('#exportExcelBtn').toggle(!!period);

        const year = $('#exportYear').val();
        const month = $('#exportMonth').val();

        let show = false;
        if (period === 'yearly' && year) show = true;
        if (period === 'monthly' && year && month) show = true;

        $('#exportExcelBtn').toggle(show);
    });

    $(document).on('change', '#exportYear, #exportMonth', function () {
        const period = $('#exportPeriod').val();
        const year = $('#exportYear').val();
        const month = $('#exportMonth').val();

        let show = false;
        if (period === 'yearly' && year) show = true;
        if (period === 'monthly' && year && month) show = true;

        $('#exportExcelBtn').toggle(show);
    });

    fillCurrentMonthMetricsOnce();

    $(document).on('click', '#exportExcelBtn', function () {
        const period = $('#exportPeriod').val();
        const selectedYear = $('#exportYear').val();
        const selectedMonth = $('#exportMonth').val();

        if (!period || !selectedYear) {
            alert('Please select a period and year.');
            return;
        }

        if (period === 'monthly' && !selectedMonth) {
            alert('Please select a month.');
            return;
        }

        const idxs = table.rows({ search: 'applied' }).indexes().toArray();
        const grouped = new Map();

        (window.allVehicles || []).forEach(v => {
            const vehicle = String(v.vehicle || '').trim();
            const company = String(v.company || '').trim();

            if (!vehicle) return;

            const key = vehicle;

            if (!grouped.has(key)) {
                grouped.set(key, {
                    label: buildPeriodLabel(period, selectedYear, selectedMonth),
                    vehicle,
                    company,
                    usedDays: 0
                });
            }
        });

        idxs.forEach(i => {
            const node = table.row(i).node();
            const d = node?.dataset || {};

            const vehicle = String(d.vehicle || '').trim();
            const company = String(d.company || '').trim();
            const arrival = String(d.arrival || '').trim();
            const departure = String(d.departure || '').trim();

            if (!vehicle || !arrival || !departure) return;

            const key = vehicle;

            if (!grouped.has(key)) {
                grouped.set(key, {
                    label: buildPeriodLabel(period, selectedYear, selectedMonth),
                    vehicle,
                    company,
                    usedDays: 0
                });
            }

            const usedDays = calculateUsedDaysWithinSelectedPeriod(
                arrival,
                departure,
                period,
                selectedYear,
                selectedMonth
            );

            grouped.get(key).usedDays += usedDays;
        });

        const totalDaysInPeriod = getTotalDaysInPeriod(period, selectedYear, selectedMonth);

        const rows = [...grouped.values()].map(item => {
            const dayPctNum = totalDaysInPeriod > 0
                ? ((item.usedDays / totalDaysInPeriod) * 100)
                : 0;

                const status = getUtilizationStatus(dayPctNum);

                return {
                    periodLabel: item.label,
                    vehicle: item.vehicle,
                    company: item.company,
                    usedDays: item.usedDays,
                    dayPct: dayPctNum.toFixed(2) + '%',
                    status
                };

        }).sort((a, b) => {
            if (b.usedDays !== a.usedDays) return b.usedDays - a.usedDays;
            return a.vehicle.localeCompare(b.vehicle);
        });

        const periodLabel = buildPeriodLabel(period, selectedYear, selectedMonth);
        const suffix = period === 'monthly'
            ? `${selectedYear}_${selectedMonth}`
            : `${selectedYear}`;

        exportVehicleAnalyticsExcel({
            rows,
            periodLabel,
            totalDaysInPeriod,
            filename: `vehicle_usage_${period}_${suffix}.xlsx`
        });
    });

    function fillCurrentMonthMetricsOnce() {
        const period = 'monthly';
        const year = currentYear;
        const month = currentMonth;
        const totalDaysInPeriod = getTotalDaysInPeriod(period, year, month);

        table.rows().every(function () {
            const node = this.node();
            const d = node?.dataset || {};

            const arrival = String(d.arrival || '').trim();
            const departure = String(d.departure || '').trim();

            const usedDays = calculateUsedDaysWithinSelectedPeriod(
                arrival,
                departure,
                period,
                year,
                month
            );

            const dayPctNum = totalDaysInPeriod > 0
                ? ((usedDays / totalDaysInPeriod) * 100)
                : 0;

            $(node).find('.used-days-cell').text(usedDays);
            $(node).find('.day-utilization-cell').text(dayPctNum.toFixed(2) + '%');
        });
    }


    
    function collectYears() {
        const years = new Set();

        table.rows().every(function () {
            const node = this.node();
            const d = node?.dataset || {};

            const arrivalParts = getDateParts(d.arrival || '');
            const departureParts = getDateParts(d.departure || '');
            const createdParts = getDateParts(d.created || '');

            if (arrivalParts?.year) years.add(arrivalParts.year);
            if (departureParts?.year) years.add(departureParts.year);
            if (createdParts?.year) years.add(createdParts.year);
        });

        years.add(String(new Date().getFullYear()));
        return [...years].sort((a, b) => Number(b) - Number(a));
    }

    function getDateParts(dt) {
        const [datePart] = String(dt || '').split(' ');
        const [year, month, day] = String(datePart || '').split('-');

        if (!year || !month || !day) return null;
        return { year, month, day };
    }

    function buildPeriodLabel(period, year, month) {
        return period === 'monthly' ? `${monthName(month)} ${year}` : year;
    }

    function getTotalDaysInPeriod(period, year, month) {
        if (period === 'monthly') {
            return daysInMonth(Number(year), Number(month));
        }
        return isLeapYear(Number(year)) ? 366 : 365;
    }

    function calculateUsedDaysWithinSelectedPeriod(arrival, departure, period, year, month) {
        if (!arrival || !departure) return 0;

        const start = toDateOnly(arrival);
        const end = toDateOnly(departure);

        if (!start || !end) return 0;

        let periodStart;
        let periodEnd;

        if (period === 'monthly') {
            periodStart = new Date(Number(year), Number(month) - 1, 1);
            periodEnd = new Date(Number(year), Number(month), 0);
        } else {
            periodStart = new Date(Number(year), 0, 1);
            periodEnd = new Date(Number(year), 11, 31);
        }

        const overlapStart = start > periodStart ? start : periodStart;
        const overlapEnd = end < periodEnd ? end : periodEnd;

        if (overlapEnd < overlapStart) return 0;

        return Math.floor((overlapEnd - overlapStart) / 86400000) + 1;
    }

    function toDateOnly(str) {
        const raw = String(str).trim().replace(' ', 'T');
        const d = new Date(raw);

        if (isNaN(d.getTime())) return null;
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function monthName(num) {
        const m = {
            '01': 'January',
            '02': 'February',
            '03': 'March',
            '04': 'April',
            '05': 'May',
            '06': 'June',
            '07': 'July',
            '08': 'August',
            '09': 'September',
            '10': 'October',
            '11': 'November',
            '12': 'December'
        };
        return m[num] || num;
    }

    function daysInMonth(year, month) {
        return new Date(year, month, 0).getDate();
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    }

    function exportVehicleAnalyticsExcel({ rows, periodLabel, totalDaysInPeriod, filename }) {
        const data = [
            ['Vehicle Utilization Analytics'],
            [`Period: ${periodLabel}`],
            [],
            ['Explanation'],
            [`Used Days = Number of days the vehicle was utilized within ${periodLabel}.`],
            [`Day Utilization % = (Vehicle Used Days / Total Days in ${periodLabel}) × 100.`],
            [`Total Days in Period = ${totalDaysInPeriod}`],
            [],
            ['Period', 'Vehicle No', 'Company', 'Used Days', 'Day Utilization %', 'Utilization Status']
        ];

        rows.forEach(r => {
            data.push([
                r.periodLabel,
                r.vehicle,
                r.company,
                r.usedDays,
                r.dayPct,
                r.status
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(data);

        ws['!cols'] = [
            { wch: 18 },
            { wch: 18 },
            { wch: 24 },
            { wch: 12 },
            { wch: 18 },
            { wch: 18 }
        ];

        ws['!rows'] = [
            { hpt: 22 },
            { hpt: 20 },
            { hpt: 10 },
            { hpt: 20 },
            { hpt: 18 },
            { hpt: 18 },
            { hpt: 18 },
            { hpt: 10 },
            { hpt: 20 }
        ];

        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 5 } },
            { s: { r: 1, c: 0 }, e: { r: 1, c: 5 } },
            { s: { r: 3, c: 0 }, e: { r: 3, c: 5 } },
            { s: { r: 4, c: 0 }, e: { r: 4, c: 5 } },
            { s: { r: 5, c: 0 }, e: { r: 5, c: 5 } },
            { s: { r: 6, c: 0 }, e: { r: 6, c: 5 } }
        ];

        styleRangeWhite(ws, 0, 0, data.length - 1, 5);

        styleRow(ws, 0, 0, 5, {
            fill: solidFill('1F4E78'),
            font: { bold: true, sz: 16, color: { rgb: 'FFFFFF' }, name: 'Arial' },
            alignment: { horizontal: 'center', vertical: 'center' },
            border: fullBorder('1F4E78')
        });

        styleRow(ws, 1, 0, 5, {
            fill: solidFill('D9EAF7'),
            font: { bold: true, sz: 11, color: { rgb: '1F1F1F' }, name: 'Arial' },
            alignment: { horizontal: 'left', vertical: 'center' },
            border: fullBorder('B4C7E7')
        });

        for (let r = 3; r <= 6; r++) {
            styleRow(ws, r, 0, 5, {
                fill: solidFill('C6EFCE'),
                font: { name: 'Arial', sz: 10, color: { rgb: '006100' }, bold: r === 3 },
                alignment: { horizontal: 'left', vertical: 'center', wrapText: true },
                border: fullBorder('9BBB59')
            });
        }

        styleRow(ws, 8, 0, 5, {
            fill: solidFill('2F75B5'),
            font: { bold: true, color: { rgb: 'FFFFFF' }, name: 'Arial', sz: 10 },
            alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
            border: fullBorder('1F1F1F')
        });

        for (let r = 9; r < data.length; r++) {
            styleRow(ws, r, 0, 5, {
                fill: solidFill('FFFFFF'),
                font: { name: 'Arial', sz: 10, color: { rgb: '000000' } },
                alignment: { horizontal: 'left', vertical: 'center', wrapText: false },
                border: fullBorder('D9D9D9')
            });

            styleCell(ws, XLSX.utils.encode_cell({ r, c: 3 }), {
                alignment: { horizontal: 'right', vertical: 'center' }
            });

            styleCell(ws, XLSX.utils.encode_cell({ r, c: 4 }), {
                alignment: { horizontal: 'right', vertical: 'center' }
            });

            const statusRef = XLSX.utils.encode_cell({ r, c: 5 });
            const statusVal = ws[statusRef] ? String(ws[statusRef].v) : '';

            if (statusVal === 'Good') {
                styleCell(ws, statusRef, {
                    fill: solidFill('C6EFCE'), // green
                    font: { bold: true, color: { rgb: '006100' }, name: 'Arial' },
                    alignment: { horizontal: 'center', vertical: 'center' },
                    border: fullBorder('D9D9D9')
                });

            } else if (statusVal === 'Fair') {
                styleCell(ws, statusRef, {
                    fill: solidFill('FFF2CC'), // yellow
                    font: { bold: true, color: { rgb: '7F6000' }, name: 'Arial' },
                    alignment: { horizontal: 'center', vertical: 'center' },
                    border: fullBorder('D9D9D9')
                });

            } else if (statusVal === 'Under Utilized') {
                styleCell(ws, statusRef, {
                    fill: solidFill('F4CCCC'), // red
                    font: { bold: true, color: { rgb: '9C0006' }, name: 'Arial' },
                    alignment: { horizontal: 'center', vertical: 'center' },
                    border: fullBorder('D9D9D9')
                });

            } else if (statusVal === 'Not Utilized') {
                styleCell(ws, statusRef, {
                    fill: solidFill('D9D9D9'), // gray
                    font: { bold: true, color: { rgb: '000000' }, name: 'Arial' },
                    alignment: { horizontal: 'center', vertical: 'center' },
                    border: fullBorder('D9D9D9')
                });
            }
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Vehicle Analytics');
        XLSX.writeFile(wb, filename);
    }

    function styleRangeWhite(ws, startRow, startCol, endRow, endCol) {
        for (let r = startRow; r <= endRow; r++) {
            for (let c = startCol; c <= endCol; c++) {
                const ref = XLSX.utils.encode_cell({ r, c });
                if (!ws[ref]) {
                    ws[ref] = { t: 's', v: '' };
                }
                ws[ref].s = mergeStyle(ws[ref].s, {
                    fill: solidFill('FFFFFF'),
                    font: { name: 'Arial', sz: 10, color: { rgb: '000000' } },
                    alignment: { vertical: 'center' },
                    border: fullBorder('D9D9D9')
                });
            }
        }
    }

    function getUtilizationStatus(dayPctNum) {
        if (dayPctNum === 0) return 'Not Utilized';
        if (dayPctNum > 75) return 'Good';
        if (dayPctNum >= 50) return 'Fair';
        return 'Under Utilized';
    }

    function styleRow(ws, rowIndex, startCol, endCol, style) {
        for (let c = startCol; c <= endCol; c++) {
            const ref = XLSX.utils.encode_cell({ r: rowIndex, c });
            if (!ws[ref]) {
                ws[ref] = { t: 's', v: '' };
            }
            ws[ref].s = mergeStyle(ws[ref].s, style);
        }
    }

    function styleCell(ws, ref, style) {
        if (!ws[ref]) {
            ws[ref] = { t: 's', v: '' };
        }
        ws[ref].s = mergeStyle(ws[ref].s, style);
    }

    function mergeStyle(base, extra) {
        return {
            font: { ...(base?.font || {}), ...(extra?.font || {}) },
            fill: { ...(base?.fill || {}), ...(extra?.fill || {}) },
            alignment: { ...(base?.alignment || {}), ...(extra?.alignment || {}) },
            border: { ...(base?.border || {}), ...(extra?.border || {}) }
        };
    }

    function solidFill(rgb) {
        return {
            patternType: 'solid',
            fgColor: { rgb }
        };
    }

    function fullBorder(color) {
        return {
            top: { style: 'thin', color: { rgb: color } },
            bottom: { style: 'thin', color: { rgb: color } },
            left: { style: 'thin', color: { rgb: color } },
            right: { style: 'thin', color: { rgb: color } }
        };
    }
});
</script>
@endsection