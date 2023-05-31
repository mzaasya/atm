<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ATM</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <style>
        body {
            padding: 0;
            margin: 0;
            overflow-x: hidden;
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-6">
            <div class="card shadow border-0">
                <div class="card-header text-center"><b>ATM Pecahan 50.000 & 100.000</b></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-2">
                                <label for="machine" class="text-muted">Mesin ATM</label>
                                <select name="machine" id="machine" class="form-select">
                                    @foreach ($machines as $machine)
                                        <option value="{{$machine->id}}" data-balance="{{$machine->balance}}">{{$machine->code . ' ('.$machine->location.')'}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="type" class="text-muted">Jenis Transaksi</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="withdraw">Penarikan Tunai</option>
                                    <option value="deposit">Setor Tunai</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-2">
                                <label for="card" class="text-muted">No. Rekenening</label>
                                <input type="number" id="card" name="card" class="form-control" value="1122334455667788">
                            </div>
                            <div class="mb-2">
                                <label for="pin" class="text-muted">PIN</label>
                                <input type="number" id="pin" name="pin" class="form-control" value="123456">
                            </div>
                        </div>
                        <div class="col-12">    
                            <div class="mb-3">
                                <label for="value" class="text-muted">Jumlah</label>
                                <input type="number" id="value" name="value" class="form-control">
                                <div><small class="text-muted" id="user-balance"></small></div>
                                <div><small class="text-muted" id="machine-balance"></small></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="button" id="btn-submit" class="btn btn-primary">Transaksi</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow border-0">
                <div class="card-header text-center"><b>Riwayat Transaksi</b></div>
                <div class="card-body p-2">
                    <ul class="list-group" id="history"></ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" id="modal-loading" data-bs-backdrop="static">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
          </div>
        </div>
    </div>
    <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center w-100">
        <div id="toast-result" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body" id="toast-result-message"></div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script>
        const token = $('meta[name="csrf-token"]').attr('content');
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });

        $(document).ready(function() {
            getTransactions();

            $('#machine').on('change', function() {
                const balance = $(this).find(':selected').data('balance');
                $('#machine-balance').html('Sisa saldo ATM yang dipilih: <b>' + formatter.format(balance) + '</b>');
            }).change();
            
            $('#card, #pin').on('change', function() {
                const card = $('#card').val();
                const pin = $('#pin').val();
                if (!isCardValid(card)) {
                    showToast('danger', 'Nomor rekening harus 16 digit.');
                    return;
                }
                if (!isPinValid(pin)) {
                    showToast('danger', 'PIN harus 6 digit.');
                    return;
                }
                startLoading();
                $.ajax({
                    url: '/user',
                    type: 'post',
                    dataType: 'json',
                    data: {card, pin},
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data) {
                        stopLoading();
                        if(data){
                            $('#user-balance').html('Sisa saldo anda: <b>' + formatter.format(data.balance) + '</b>');
                        }else{
                            $('#user-balance').html('Sisa saldo anda: <b>' + formatter.format(0) + '</b>');
                            showToast('danger', 'Data user tidak ditemukan.')
                        }
                    }
                })
            }).change();

            $('#btn-submit').on('click', function() {
                const card = $('#card').val();
                const pin = $('#pin').val();
                const type = $('#type').val();
                const machine = $('#machine').val();
                const value = $('#value').val();
                const balance = $('#machine').find(':selected').data('balance');
                if (!isCardValid(card)) {
                    showToast('danger', 'Nomor rekening harus 16 digit.');
                    return;
                }
                if (!isPinValid(pin)) {
                    showToast('danger', 'PIN harus 6 digit.');
                    return;
                }
                if (!isValueValid(value)) {
                    showToast('danger', 'Transaksi hanya dapat dilakukan dengan kelipatan 50.000 atau 100.0000');
                    return;
                }
                if (type == 'withdraw' && !isBalanceEnough(value, balance)) {
                    showToast('danger', 'Saldo ATM tidak mencukupi, pastikan jumlah penarikan tidak lebih dari ' + balance);
                    return;
                }
                startLoading();
                $.ajax({
                    url: '/',
                    type: 'post',
                    dataType: 'json',
                    data: {card, pin, type, value, machine},
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data) {
                        const type = data.statusCode == 400 ? 'danger' : 'success';
                        stopLoading();
                        getMachines();
                        getTransactions();
                        showToast(type, data.message);
                        $('#machine, #card').trigger('change');
                    }
                })
            })
        })

        function isCardValid(card) {
            return card.toString().length === 16;
        }

        function isPinValid(pin) {
            return pin.toString().length === 6;
        }

        function isValueValid(value) {
            return value > 0 && (value % 50000 === 0 || value % 100000 === 0);
        }
        
        function isBalanceEnough(value, balance) {
            return balance >= value;
        }

        function startLoading() {
            $('#modal-loading').modal('show');
        }
        
        function stopLoading() {
            $('#modal-loading').modal('hide');
        }

        function showToast(type = 'success', message = '') {
            const bgClass = 'text-bg-' + type;
            $('#toast-result-message').html(message);
            $('#toast-result').removeClass('text-bg-success text-bg-danger');
            $('#toast-result').addClass(bgClass);
            $('#toast-result').toast('show');
        }

        function getMachines() {
            startLoading();
            $.ajax({
                url: '/machines',
                type: 'get',
                dataType: 'json',
                headers: {'X-CSRF-TOKEN': token},
                success: function(data) {
                    stopLoading();
                    let list = '';
                    const selectedMachine = $('#machine').find(':selected').val();
                    for (const d of data) {
                        list += `<option value="${d.id}" data-balance="${d.balance}" ${d.id == selectedMachine ? 'selected' : ''}>${d.code} (${d.location})</option>`;
                    }
                    $('#machine').html(list);
                    $('#machine').trigger('change');
                }
            })
        }
        
        function getTransactions() {
            startLoading();
            $.ajax({
                url: '/transactions',
                type: 'get',
                dataType: 'json',
                headers: {'X-CSRF-TOKEN': token},
                success: function(data) {
                    stopLoading();
                    let list = '';
                    for (const d of data) {
                        list += `<li class="list-group-item text-center p-2">
                            <div class="text-muted" style="font-size: 0.7rem;">${d.created_at}</div>
                            <div style="font-size: 0.8rem;">${d.card_number}</div>
                            <div style="font-size: 0.8rem;">${d.type.toUpperCase()}</div>
                            <div style="font-size: 0.8rem;"><b>${formatter.format(d.value)}</b></div>
                            <div style="font-size: 0.8rem;">${d.code} (${d.location})</div>
                        </li>`;
                    }
                    $('#history').html(list);
                    if(data.length < 1){
                        $('#history').html('<li class="list-group-item text-center text-muted p-2">No transactions</li>');
                    }
                }
            })
        }
    </script>
</body>

</html>