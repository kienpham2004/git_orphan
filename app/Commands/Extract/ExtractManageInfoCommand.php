<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Models\ManageInfo;
use App\Models\Views\VManageInfo;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * マネジメントインフォの取り込み処理
 * tb_manage_info
 *
 * @author yhatsutori
 *
 */
class ExtractManageInfoCommand extends Command implements ShouldBeQueued{

    /*
     * 前後の月数
     */
    const FROM_TERMS = 1; // -2 month
    const TO_TERMS = 8;

    protected $targetYm;
    protected $targetInspectDivs;

    /**
     * コンストラクタ
     * @param [type] $targetYm          対象月
     * @param [type] $targetInspectDivs 車点検
     */
    public function __construct( $targetYm=null, $targetInspectDivs=null ){
        $this->targetYm = $targetYm;
        $this->targetInspectDivs = $targetInspectDivs;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        
        echo PHP_EOL.'class ------------- ： '.__CLASS__;
        echo PHP_EOL.'line -------------- ： '.__LINE__;
        echo PHP_EOL."開始前[メモリ使用量]： ".memory_get_usage() / (1024 * 1024) ."MB\n";

        // メモリの設定
        ini_set('memory_limit', '4096M');
        echo date('Y-m-d H:i:s') ." - ExtractManageInfoCommand start";
        $from = "";
        $to = "";

        // 対象月の指定がない場合
        if( empty( $this->targetYm ) == True ) {
            $current = DateUtil::currentYm();
            $from = DateUtil::monthAgo($current, static::FROM_TERMS, 'Ym');
            $to = DateUtil::monthLater($current, static::TO_TERMS, 'Ym');

        // 対象月の指定がある場合
        } else {
            // 対象月を設定する
            $from = $to = $this->targetYm;
        }

        // まとめて更新を行う値を格納する配列
        $storeTargets = array();

//        // 点検区分の指定がない場合は全てを対象とする
//        if( empty( $this->targetInspectDivs ) == True ) {
//
//            $this->findAndMerge( $from, $to, $storeTargets );
//
//        // 点検区分の指定がある場合はそれのみを対象とする
//        } else {
//
//            $this->findAndMerge( $from, $to, $storeTargets );
//        }
        $this->findAndMerge( $from, $to, $storeTargets );
            echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMerge end";

        \Log::debug('>>>> store target list');
        //\Log::debug($storeTargets);

            echo PHP_EOL.'line ------------------ ： '.__LINE__;
            echo PHP_EOL."ManageInfo::merge開始前 ： ".memory_get_usage() / (1024 * 1024) ."MB\n";
        
        // データを抽出
        foreach( $storeTargets as $target ){
            //2022/03/29 update if dsya_status_katsudo_csv and dsya_status_katsudo_code null then unset
            if (!isset($target['mi_dsya_status_katsudo_code']) || $target['mi_dsya_status_katsudo_code'] == null){
               unset($target['mi_dsya_status_katsudo_code']);
               unset($target['mi_dsya_status_katsudo_csv']);
               unset($target['mi_dsya_status_katsudo_date']);

            }
            ManageInfo::merge( $target );
        }

        // リコールフラグ更新
        $this->recallFlagUpdate();

        echo PHP_EOL.'line ------------------ ： '.__LINE__;
        echo PHP_EOL."ManageInfo::merge終了後 ： ".memory_get_usage() / (1024 * 1024) ."MB\n";

        echo PHP_EOL.date('Y-m-d H:i:s') ." - ExtractManageInfoCommand end";
    }

    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMerge( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VManageInfo::findTarget( $from, $to );
            echo PHP_EOL.'line ------------------ ： '.__LINE__;
            echo PHP_EOL."findTarget(終了後)      ： ".memory_get_usage() / (1024 * 1024) ."MB\n";

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
            echo PHP_EOL.'line ------------------ ： '.__LINE__;
            echo PHP_EOL."array_merge(終了後)     ： ".memory_get_usage() / (1024 * 1024) ."MB\n";
        }
    }

    /**
     * リコールフラグ更新の処理を行う
     */
    private function recallFlagUpdate(){
        // フラグを付ける
        $sql = " WITH tmp_recall AS (
                 --リコール未対応
                 select distinct(recall_car_manage_number) 
                 from tb_recall
                 where recall_jisshibi is null
                )
                --フラグを付ける
                update tb_manage_info set mi_rcl_recall_flg = 1
                where id in (
                    select m.id 
                    from tb_manage_info m inner join tmp_recall r  
                        on r.recall_car_manage_number = m.mi_car_manage_number
                    where m.mi_rcl_recall_flg is null  
                )";

        \DB::statement( $sql );

        // フラグを削除する
        $sql = "  WITH tmp_recall AS (
                 --リコール対応済み
                 select distinct(recall_car_manage_number) 
                 from tb_recall
                 where recall_car_manage_number not in (
                     select distinct(recall_car_manage_number) 
                     from tb_recall
                     where recall_jisshibi is null
                    )
                )
                --フラグを削除する
                update tb_manage_info set mi_rcl_recall_flg = null
                where id in (
                    select m.id  
                    from tb_manage_info m inner join tmp_recall r
                        on r.recall_car_manage_number = m.mi_car_manage_number
                    where mi_rcl_recall_flg = 1  
                )";

        \DB::statement( $sql );
    }
}
