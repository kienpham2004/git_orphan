<?php

namespace App\Commands\Extract;

use App\Models\IkouHistory;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 意向履歴更新
 */
class UpdateIkouHistoryCommand extends Command implements ShouldBeQueued{

    /**
     * コンストラクタ
     * @param string $targetYm 対象月
     */
    public function __construct( ){
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 意向結果一覧を取得
        $ikouValues = $this->getIkouValues();

        foreach( $ikouValues as $data ){
            // $dataがarrayではなくobjectなのでキャストしてます。
            $data = (array)$data;
            IkouHistory::merge( $data);
        }
    }

    /**
     * データ一覧を取得
     * @param  string $targetYm [description]
     * @return [type]             [description]
     */
    private function getIkouValues(){
        // 作成を見直す
        $sql = "    
            WITH tmp_daigae_syaken AS (
                SELECT 
                    dsya_customer_code,
                    dsya_car_manage_number,
                    dsya_inspection_ym,
                    dsya_keiyaku_car,
                    dsya_syaken_jisshi_date,
                    dsya_syaken_reserve_date,
                    dsya_syaken_next_date,
                    dsya_status_code,
                    dsya_status_csv,
                    dsya_status_katsudo_code,
                    dsya_status_katsudo_csv,
                    --CASE
                    --    WHEN
                    --        (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NOT NULL) AND dsya_status_katsudo_date <= dsya_status_date 
                    --        THEN dsya_status_date
                    --    WHEN
                    --        (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NOT NULL) AND dsya_status_katsudo_date > dsya_status_date 
                    --        THEN dsya_status_katsudo_date
                    --    WHEN
                    --         (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NULL)
                    --        THEN dsya_status_katsudo_date
                    --    WHEN
                    --         (dsya_status_katsudo_date IS NULL AND dsya_status_date IS NOT NULL)
                    --        THEN dsya_status_date
                    --    ELSE NULL
                    --END as dsya_katsudo_date,    
                    dsya_status_katsudo_date as dsya_katsudo_date,

                    --CASE
                    --    WHEN
                    --        (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NOT NULL) AND dsya_status_katsudo_date <= dsya_status_date 
                    --        THEN dsya_status_code
                    --    WHEN
                    --        (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NOT NULL) AND dsya_status_katsudo_date > dsya_status_date 
                    --        THEN dsya_status_katsudo_code
                    --    WHEN
                    --         (dsya_status_katsudo_date IS NOT NULL AND dsya_status_date IS NULL)
                    --        THEN dsya_status_katsudo_code
                    --    WHEN
                    --         (dsya_status_katsudo_date IS NULL AND dsya_status_date IS NOT NULL)
                    --        THEN dsya_status_code
                    --    ELSE NULL
                    --END as dsya_katsudo_code
                    dsya_status_katsudo_code as dsya_katsudo_code
                    
                FROM tb_daigae_syaken
            )
            SELECT  dsya_customer_code, 
                    dsya_car_manage_number,
                    dsya_inspection_ym,
                    dsya_syaken_next_date,
                    dsya_katsudo_date,
                    dsya_katsudo_code,
                    dsya_status_code,
                    dsya_status_csv,
                    dsya_status_katsudo_code,
                    dsya_status_katsudo_csv,                    
                    CASE
                        WHEN dsya_inspection_ym = to_char(current_date, 'yyyymm') THEN 0
                        WHEN to_char(dsya_syaken_next_date + '-1 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 1
                        WHEN to_char(dsya_syaken_next_date + '-2 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 2
                        WHEN to_char(dsya_syaken_next_date + '-3 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 3
                        WHEN to_char(dsya_syaken_next_date + '-4 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 4
                        WHEN to_char(dsya_syaken_next_date + '-5 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 5
                        WHEN to_char(dsya_syaken_next_date + '-6 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm') THEN 6
                    END  AS dsya_ikou_time
            FROM tmp_daigae_syaken
            WHERE 
               ((dsya_inspection_ym = to_char(current_date, 'yyyymm')
            OR to_char(dsya_syaken_next_date + '-1 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm')	
            OR to_char(dsya_syaken_next_date + '-2 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm')
            OR to_char(dsya_syaken_next_date + '-3 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm')
            OR to_char(dsya_syaken_next_date + '-4 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm')
            OR to_char(dsya_syaken_next_date + '-5 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm')
            OR to_char(dsya_syaken_next_date + '-6 mons'::interval, 'yyyymm') = to_char(current_date, 'yyyymm'))) 
            AND 
            (
                ( dsya_katsudo_date IS NULL AND dsya_katsudo_code IS NOT NULL )
                OR 
                ( dsya_katsudo_date IS NOT NULL AND dsya_katsudo_code IS NOT NULL )
            )
            ORDER BY 
                dsya_inspection_ym, dsya_customer_code,
                dsya_car_manage_number,dsya_ikou_time           
                ";

        return \DB::select( $sql );
    }
}