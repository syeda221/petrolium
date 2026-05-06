@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Party Balance Report</h4>
                    <h6>Accurate closing balances for all parties</h6>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <form id="balanceForm" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Party Type</label>
                            <select id="type" class="form-select border-2">
                                <option value="all">All Parties</option>
                                <option value="customer">Customers Only</option>
                                <option value="vendor">Vendors Only</option>
                                <option value="dual">Dual Parties Only</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="partySelectWrapper">
                            <label class="form-label fw-bold">Specific Party</label>
                            <select id="party_id" class="form-select border-2 select2">
                                <option value="all">All Parties</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnFetch" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fas fa-search me-1"></i> Generate
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnPrint" class="btn btn-danger w-100 py-2 fw-bold" style="display:none;">
                                <i class="fas fa-file-pdf me-1"></i> Print Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Area -->
            <div id="reportBox" style="display:none;">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div id="printableArea" class="p-4">
                            <!-- Professional Header -->
                            <div class="text-center mb-4">
                                <h2 class="fw-bold mb-0" style="color: #000; letter-spacing: 1px;">AL-OWAIS PETROLEUM SERVICE</h2>
                                <p class="mb-2 fw-bold" style="color: #000;"> , Business Management & Financial Dashboard</p>
                                <div class="ledger-title mt-3">PARTY BALANCE SHEET</div>
                                <div class="d-flex justify-content-between mt-3 px-2 fw-bold" style="color: #000; font-size: 14px;">
                                    <span>REPORT TYPE: <span id="reportTypeLabel">ALL PARTIES</span></span>
                                    <span>DATE: {{ date('d-m-Y H:i') }}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered border-dark align-middle" id="balanceTable">
                                    <thead class="bg-light text-dark border-dark">
                                        <tr style="border-bottom: 3px solid #000;">
                                            <th class="text-center" width="50" style="border: 2px solid #000 !important;">#</th>
                                            <th style="border: 2px solid #000 !important;">PARTY NAME</th>
                                            <th class="text-center" style="border: 2px solid #000 !important;">TYPE</th>
                                            <th class="text-center" style="border: 2px solid #000 !important;">CONTACT</th>
                                            <th style="border: 2px solid #000 !important;">ADDRESS</th>
                                            <th class="text-end" style="border: 2px solid #000 !important;">CLOSING BALANCE</th>
                                        </tr>
                                    </thead>
                                    <tbody id="balanceBody">
                                        <!-- Data injected here -->
                                    </tbody>
                                    <tfoot id="balanceFoot">
                                        <!-- Totals injected here -->
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loader -->
            <div id="loader" style="display:none;" class="text-center my-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-3 text-muted">Calculating accurate balances...</h5>
            </div>

        </div>
    </div>
</div>

<style>
    .select2-container .select2-selection--single { height: 38px !important; border: 2px solid #dee2e6 !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    
    .ledger-title {
        text-align: center;
        font-weight: 800;
        font-size: 24px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #000;
        border-top: 3px solid #000;
        border-bottom: 3px solid #000;
        padding: 8px 0;
    }

    #balanceTable { border: 3px solid #000 !important; }
    #balanceTable thead th { 
        background: #f1f5f9 !important; 
        color: #000 !important; 
        font-weight: 800 !important;
        text-transform: uppercase;
    }
    #balanceTable tbody td { 
        padding: 12px; 
        border: 2px solid #000 !important;
        color: #000;
        font-weight: 500;
    }
    .dr-text { color: #10b981; font-weight: 800; }
    .cr-text { color: #ef4444; font-weight: 800; }
    .total-row { background: #f1f5f9; font-weight: 900; font-size: 1.2rem; border-top: 3px solid #000 !important; }
    .total-row td { border: 2px solid #000 !important; color: #000 !important; }
    
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        #balanceTable { width: 100% !important; }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const customers = @json($customers);
    const vendors = @json($vendors);

    // Initialize Select2
    if ($('.select2').length) {
        $('.select2').select2({
            placeholder: 'Select Party',
            width: '100%'
        });
    }

    // Filter parties based on type
    $('#type').change(function() {
        const type = $(this).val();
        const partySelect = $('#party_id');
        partySelect.empty().append('<option value="all">All Parties</option>');

        if (type === 'all') {
            // Keep it All
        } else if (type === 'customer' || type === 'dual') {
            customers.forEach(c => {
                partySelect.append(`<option value="${c.id}">${c.customer_name}</option>`);
            });
        } else if (type === 'vendor') {
            vendors.forEach(v => {
                partySelect.append(`<option value="${v.id}">${v.name}</option>`);
            });
        }
        partySelect.trigger('change');
    });

    // Prevent Form Submit
    $('#balanceForm').on('submit', function(e) {
        e.preventDefault();
        $('#btnFetch').click();
    });

    // Fetch balances
    $('#btnFetch').click(function(e) {
        e.preventDefault();
        const type = $('#type').val();
        const partyId = $('#party_id').val();
        const typeText = $('#type option:selected').text().toUpperCase();
        
        $('#reportTypeLabel').text(typeText);
        $('#reportBox').hide();
        $('#loader').show();
        $('#btnPrint').hide();

        $.get("{{ route('report.party_balances.fetch') }}", { type, party_id: partyId }, function(res) {
            $('#loader').hide();
            $('#reportBox').show();
            $('#btnPrint').show();

            let html = '';
            res.results.forEach((r, i) => {
                const sideCls = r.balance >= 0 ? 'dr-text' : 'cr-text';
                const sideText = r.balance >= 0 ? '(Dr)' : '(Cr)';
                html += `
                    <tr>
                        <td class="text-center text-muted">${i+1}</td>
                        <td class="fw-bold">${r.name}</td>
                        <td class="text-center"><span class="badge bg-secondary">${r.type}</span></td>
                        <td class="text-center">${r.mobile || '-'}</td>
                        <td class="text-muted small">${r.address || '-'}</td>
                        <td class="text-end fw-bolder ${sideCls}">
                            Rs. ${Math.abs(r.balance).toLocaleString('en-PK', {minimumFractionDigits:2})} ${sideText}
                        </td>
                    </tr>
                `;
            });

            if(res.results.length === 0) {
                html = '<tr><td colspan="6" class="text-center py-4">No parties found matching criteria.</td></tr>';
            }

            $('#balanceBody').html(html);

            const totalSideCls = res.total >= 0 ? 'dr-text' : 'cr-text';
            const totalSideText = res.total >= 0 ? '(Dr)' : '(Cr)';
            $('#balanceFoot').html(`
                <tr class="total-row">
                    <td colspan="5" class="text-end text-dark">GRAND TOTAL BALANCE:</td>
                    <td class="text-end ${totalSideCls}">
                        Rs. ${Math.abs(res.total).toLocaleString('en-PK', {minimumFractionDigits:2})} ${totalSideText}
                    </td>
                </tr>
            `);
        });
    });

    // Print functionality
    $('#btnPrint').click(function() {
        const element = document.getElementById('printableArea');
        const opt = {
            margin: [10, 10, 10, 10],
            filename: 'Party_Balance_Report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>
@endsection
