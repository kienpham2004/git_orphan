<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Models\Credit;
use App\Models\Views\VCredit;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 顧客データのcsv取り込み処理
 * tb_target_cars
 *
 * @author yhatsutori
 *
 */
class ExtractCreditCommand extends Command implements ShouldBeQueued{

    /*
     * 前後の月数
     */
    const FROM_TERMS = 0;
    const TO_TERMS = 7;

    protected $targetYm;
    protected $targetInspectDivs;

    /**
     * コンストラクタ
     * @param [type] $targetYm          対象月
     * @param [type] $targetInspectDivs 車点検
     */
    public function __construct( $targetYm=null ){
        $this->targetYm = $targetYm;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // メモリの設定
        ini_set('memory_limit', '1024M');

        $from = "";
        $to = "";

        // 対象月の指定がない場合
        if( empty( $this->targetYm ) == True ){
            $current = DateUtil::currentYm();
            $from = DateUtil::monthAgo( $current, static::FROM_TERMS, 'Ym' );
            $to = DateUtil::monthLater( $current, static::TO_TERMS, 'Ym' );

        // 対象月の指定がある場合
        } else {
            // 対象月を設定する
            $from = $to = $this->targetYm;
        }

        // まとめて更新を行う値を格納する配列
        $storeTargets = array();
        
        //データ所得
            $this->findAndMergeCredit( $from, $to, $storeTargets );


        // データを抽出
        foreach( $storeTargets as $target ){
            Credit::merge( $target );
        }

    }


    /**
     * 無料6ヶ月点検に該当するお客様を抽出
     * @param  [type] $from          [description]
     * @param  [type] $to            [description]
     * @param  [type] &$storeTargets [description]
     * @return [type]                [description]
     */
    private function findAndMergeCredit( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VCredit::findCredit( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

}
