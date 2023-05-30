<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function view()
    {
        $data = [
            'machines' => DB::table('machines')->get()
        ];
        return view('welcome', $data);
    }

    public function history()
    {
        $query = DB::table('transactions');
        $query->select([
            'transactions.id',
            'transactions.type',
            'transactions.value',
            'transactions.created_at',
            'users.card_number',
        ]);
        $query->leftJoin('machines', 'transactions.machine_id', '=', 'machines.id');
        $query->leftJoin('users', 'transactions.user_id', '=', 'users.id');
        $query->orderBy('transactions.created_at', 'desc');
        $query->limit(5);
        return json_encode($query->get());
    }

    public function transaction(Request $request)
    {
        $result = [
            'statusCode' => 400,
            'message' => '',
        ];

        // Get machine
        $machine = DB::table('machines')->find($request->input('machine'));

        // Get user
        $user = DB::table('users')->where([
            'card_number' => $request->input('card'),
            'pin_number' => $request->input('pin'),
        ])->first();

        // User validation
        if (!$user) {
            $result['message'] = 'Data user tidak ditemukan';
            return json_encode($result);
        }

        // Machine validation
        if (!$machine) {
            $result['message'] = 'Data mesin ATM tidak ditemukan';
            return json_encode($result);
        }

        // Process transaction
        $userBalance = $user->balance;
        $machineBalance = $machine->balance;

        if ($request->input('type') === 'withdraw') {
            $userBalance = $userBalance - $request->input('value');
            $machineBalance = $machineBalance - $request->input('value');
        } elseif ($request->input('type') === 'deposit') {
            $userBalance = $userBalance + $request->input('value');
            $machineBalance = $machineBalance + $request->input('value');
        }

        $processTransaction = DB::table('transactions')->insert([
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'type' => $request->input('type'),
            'value' => $request->input('value'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $processUser = DB::table('users')->where('id', $user->id)->update([
            'balance' => $userBalance,
        ]);

        $processMachine = DB::table('machines')->where('id', $machine->id)->update([
            'balance' => $machineBalance,
        ]);

        if (!$processTransaction || !$processUser || !$processMachine) {
            $result['message'] = 'Proses transaksi gagal, silahkan coba kembali.';
            return json_encode($result);
        }

        $result['statusCode'] = 201;
        $result['message'] = 'Proses transaksi berhasil';
        return json_encode($result);
    }

    public function getUser(Request $request)
    {
        $user = DB::table('users')->where([
            'card_number' => $request->input('card'),
            'pin_number' => $request->input('pin'),
        ])->first();
        return json_encode($user);
    }
}
