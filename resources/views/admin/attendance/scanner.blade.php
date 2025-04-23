@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Barcode Scanner</div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="barcode">Scan Barcode</label>
                        <input type="text" id="barcode" class="form-control" placeholder="Focus here and scan barcode" autofocus>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label>Select Type</label>
                        <div class="btn-group w-100">
                            <button id="in-button" class="btn btn-success active" data-type="in">Check In</button>
                            <button id="out-button" class="btn btn-danger" data-type="out">Check Out</button>
                        </div>
                    </div>
                    
                    <div id="result" class="mt-4">
                        <!-- Results will appear here -->
                    </div>
                    
                    <div id="history" class="mt-4">
                        <h5>Recent Scans</h5>
                        <div class="list-group" id="scan-history">
                            <!-- Recent scans will appear here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const barcodeInput = document.getElementById('barcode');
        const inButton = document.getElementById('in-button');
        const outButton = document.getElementById('out-button');
        const resultDiv = document.getElementById('result');
        const scanHistory = document.getElementById('scan-history');
        
        let scanType = 'in'; // Default scan type
        
        // Set active scan type
        inButton.addEventListener('click', function() {
            scanType = 'in';
            inButton.classList.add('active');
            outButton.classList.remove('active');
            barcodeInput.focus();
        });
        
        outButton.addEventListener('click', function() {
            scanType = 'out';
            outButton.classList.add('active');
            inButton.classList.remove('active');
            barcodeInput.focus();
        });
        
        // Process barcode on enter key
        barcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                processScan();
            }
        });
        
        function processScan() {
            const barcode = barcodeInput.value.trim();
            
            if (barcode === '') {
                return;
            }
            
            // Show loading indicator
            resultDiv.innerHTML = '<div class="alert alert-info">Processing...</div>';
            
            // Send the scan data to the server
            fetch('{{ route('attendance.scan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    barcode: barcode,
                    type: scanType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5>${data.user.name}</h5>
                            <p>Successfully recorded as ${data.user.type} at ${data.user.time}</p>
                        </div>
                    `;
                    
                    // Add to history
                    const historyItem = document.createElement('a');
                    historyItem.className = 'list-group-item list-group-item-action';
                    historyItem.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${data.user.name}</h6>
                            <small>${data.user.time}</small>
                        </div>
                        <p class="mb-1">Type: <span class="badge ${data.user.type === 'in' ? 'bg-success' : 'bg-danger'}">${data.user.type}</span></p>
                    `;
                    scanHistory.prepend(historyItem);
                    
                    // Limit history to 10 items
                    if (scanHistory.children.length > 10) {
                        scanHistory.removeChild(scanHistory.lastChild);
                    }
                } else {
                    // Show error message
                    resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
                
                // Clear input and focus
                barcodeInput.value = '';
                barcodeInput.focus();
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                barcodeInput.value = '';
                barcodeInput.focus();
            });
        }
    });
</script>
@endsection