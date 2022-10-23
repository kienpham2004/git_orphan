<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Original\Codes\Inspect\InspectSyatenkenTypes;
use App\Models\TargetCars;
use App\Models\Views\VTargetAnkai;
use App\Models\Views\VTargetHoutei12;
use App\Models\Views\VTargetMuryou6;
use App\Models\Views\VTargetSyaken;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 顧客データのcsv取り込み処理
 * tb_target_cars
 *
 * C京都
 *
 */
class ExtractTargetCarsCommand extends Command implements ShouldBeQueued{

    /*
     * 前後の月数
     */
    const FROM_TERMS = 0;
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
        
        info('class ------------- ： '.__CLASS__);
        info('line -------------- ： '.__LINE__);
        info("開始前[メモリ使用量]： ".memory_get_usage() / (1024 * 1024) ."MB\n");

        // メモリの設定
        ini_set('memory_limit', '4096M');
        echo date('Y-m-d H:i:s') ." - ExtractTargetCarsCommand start";

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

        // 点検区分の指定がない場合は全てを対象とする
        if( empty( $this->targetInspectDivs ) == True ){
            $this->findAndMergeMuryou6( $from, $to, $storeTargets );
            echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeMuryou6 end";
            info('line ----------------- ： '.__LINE__);
            info("findAndMergeMuryou6    ： ".memory_get_usage() / (1024 * 1024) ."MB\n");
            
            $this->findAndMergeHoutei12( $from, $to, $storeTargets );
            echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeHoutei12 end";
            info('line ----------------- ： '.__LINE__);
            info("findAndMergeHoutei12   ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

            $this->findAndMergeAnkai( $from, $to, $storeTargets );
            echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeAnkai end";
            info('line ----------------- ： '.__LINE__);
            info("findAndMergeAnkai      ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

            $this->findAndMergeSyaken( $from, $to, $storeTargets );
            echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeSyaken end";
            info('line ----------------- ： '.__LINE__);
            info("findAndMergeSyaken     ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

        // 点検区分の指定がある場合はそれのみを対象とする
        } else {
            // 無料６ヶ月点検の場合
            if( InspectSyatenkenTypes::isInspectM6( $this->targetInspectDivs ) ) {
                $this->findAndMergeMuryou6( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeMuryou6 end";

            // 法定１２ヶ月点検の場合
            } else if ( InspectSyatenkenTypes::isInspectH12( $this->targetInspectDivs ) ) {
                $this->findAndMergeHoutei12( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeHoutei12 end";

            // 安心快適点検の場合
            } else if ( InspectSyatenkenTypes::isInspectAK( $this->targetInspectDivs ) ) {
                $this->findAndMergeAnkai( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeAnkai end";

            // 車検の場合
            } else if ( InspectSyatenkenTypes::isInspectSK( $this->targetInspectDivs ) ) {
                $this->findAndMergeSyaken( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeSyaken end";
            }
        }
        
        // データを抽出
        foreach( $storeTargets as $target ){
            TargetCars::merge( $target );
        }
        echo PHP_EOL.date('Y-m-d H:i:s') ." - ExtractTargetCarsCommand end";
        
        info('class ------------- ： '.__CLASS__);
        info('line -------------- ： '.__LINE__);
        info("終了[メモリ使用量]  ： ".memory_get_usage() / (1024 * 1024) ."MB\n");
        
    }
    
    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeSyaken( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VTargetSyaken::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * 安心快適点検に該当するお客様を抽出
     * @param  [type] $from          [description]
     * @param  [type] $to            [description]
     * @param  [type] &$storeTargets [description]
     * @return [type]                [description]
     */
    private function findAndMergeAnkai( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VTargetAnkai::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * 法定12ヶ月点検に該当するお客様を抽出
     * @param  [type] $from          [description]
     * @param  [type] $to            [description]
     * @param  [type] &$storeTargets [description]
     * @return [type]                [description]
     */
    private function findAndMergeHoutei12( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VTargetHoutei12::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * 無料6ヶ月点検に該当するお客様を抽出
     * @param  [type] $from          [description]
     * @param  [type] $to            [description]
     * @param  [type] &$storeTargets [description]
     * @return [type]                [description]
     */
    private function findAndMergeMuryou6( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VTargetMuryou6::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

}
