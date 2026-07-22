<?php

namespace App\Services;

use App\Models\Backend\Deposit;
use App\Models\Backend\Fine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineService
{
    public function dateWiseFineList($date = null)
    {
        return Fine::with('user','imposedBy')->whereDate('date', $date)->get();
    }

    public function monthlyWiseFineList($selectedMonth = null)
    {
        $selectedMonth = $selectedMonth ?: now()->format('Y-m');

        if ($selectedMonth > now()->format('Y-m')) {
            $selectedMonth = now()->format('Y-m');
        }

        $date = \Carbon\Carbon::parse($selectedMonth);

        return Fine::with([
            'user:id,name,phone',
            'imposedBy:id,name',
            'replaceUser:id,name'
        ])
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->latest('date')
            ->get();
    }

    public function fineStore(array $data)
    {
        try {
            DB::beginTransaction();

            $typeInt = ($data['type'] === 'bazar_fine') ? 1 : 2;

            $beneficiaryIds = isset($data['beneficiary_users']) ? implode(',', $data['beneficiary_users']) : null;

            foreach ($data['users'] as $key => $userId) {
                Fine::create([
                    'user_id'         => $userId,
                    'imposed_by'      => $data['imposed_by'],
                    'date'            => $data['date'],
                    'amount'          => $data['amount'],
                    'type'            => $typeInt,
                    'reason'          => $data['note'],
                    'replace_user_id' => ($typeInt == 1) ? $data['beneficiary_users'][$key] : null,
                ]);

                if ($typeInt == 1) {
                    Deposit::create([
                        'user_id' => $userId,
                        'made_by' => $data['imposed_by'],
                        'date'    => $data['date'],
                        'amount'  => -$data['amount'],
                        'note'    => 'Bazar Fine: ' . $data['date'],
                    ]);
                }
            }

            if ($data['type'] === 'bazar_fine' && isset($data['beneficiary_users'])) {
                foreach ($data['beneficiary_users'] as $bUserId) {
                    Deposit::create([
                        'user_id' => $bUserId,
                        'made_by' => $data['imposed_by'],
                        'date'    => $data['date'],
                        'amount'  => $data['amount'],
                        'note'    => 'Bazar Reward: ' . $data['date'],
                    ]);
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Fine and Reward entries created successfully'
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function fineDelete($id)
    {
        try {
            DB::beginTransaction();
            $fine = Fine::findOrFail($id);
            $fine->delete();
            DB::commit();
            return [
                'success' => true,
                'message' => 'Fine deleted successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete fine: ' . $e->getMessage(),
            ];
        }
    }

    public function fineByUser($selectedMonth,$userId)
    {
        $date = Carbon::parse($selectedMonth);
        return Fine::with('user', 'imposedBy')
            ->where('user_id',$userId)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function allTimeFineByUser($userId)
    {
        return Fine::with('user', 'imposedBy')
            ->where('user_id',$userId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
