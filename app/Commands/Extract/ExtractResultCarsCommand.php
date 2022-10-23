<?php

namespace App\Commands\Extract;

use App\Lib\Util\DateUtil;
use App\Original\Codes\Inspect\InspectSyatenkenTypes;
use App\Models\ResultCars;
use App\Models\Views\VResultAnkai;
use App\Models\Views\VResultHoutei12;
use App\Models\Views\VResultMuryou6;
use App\Models\Views\VResultSyaken;
use App\Models\Views\VResultSyakenNext;
use App\Commands\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * 実績データのcsv取り込み処理
 * tb_result_cars
 *
 * @author yhatsutori
 *
 */
class ExtractResultCarsCommand extends Command implements ShouldBeQueued{

    /*
     * 前後の月数
     */
    const FROM_TERMS = 1; // -2 month
    const TO_TERMS = 6;

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

        // 点検区分の指定がない場合は全てを対象とする
        if( empty( $this->targetInspectDivs ) == True ) {
            $this->findAndMergeMuryou6( $from, $to, $storeTargets );
            $this->findAndMergeHoutei12( $from, $to, $storeTargets );
            $this->findAndMergeAnkai( $from, $to, $storeTargets );
            $this->findAndMergeSyaken( $from, $to, $storeTargets );
            $this->findAndMergeSyakenNext( $from, $to, $storeTargets );

        // 点検区分の指定がある場合はそれのみを対象とする
        } else {
            // 無料６ヶ月点検の場合
            if( InspectSyatenkenTypes::isInspectM6( $this->targetInspectDivs ) ) {
                $this->findAndMergeMuryou6( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeMuryou6 end";
                info('line ----------------- ： '.__LINE__);
                info("findAndMergeMuryou6    ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

            // 法定１２ヶ月点検の場合
            } else if ( InspectSyatenkenTypes::isInspectH12( $this->targetInspectDivs ) ) {
                $this->findAndMergeHoutei12( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeHoutei12 end";
                info('line ----------------- ： '.__LINE__);
                info("findAndMergeHoutei12   ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

            // 安心快適点検の場合
            } else if ( InspectSyatenkenTypes::isInspectAK( $this->targetInspectDivs ) ) {
                $this->findAndMergeAnkai( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeAnkai end";
                info('line ----------------- ： '.__LINE__);
                info("findAndMergeAnkai     ： ".memory_get_usage() / (1024 * 1024) ."MB\n");

            // 車検の場合
            } else if ( InspectSyatenkenTypes::isInspectSK( $this->targetInspectDivs ) ) {
                $this->findAndMergeSyaken( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeSyaken end";
                info('line ----------------- ： '.__LINE__);
                info("findAndMergeSyaken     ： ".memory_get_usage() / (1024 * 1024) ."MB\n");
                
                $this->findAndMergeSyakenNext( $from, $to, $storeTargets );
                echo PHP_EOL.date('Y-m-d H:i:s') ." - findAndMergeSyakenNext end";
                info('line ----------------- ： '.__LINE__);
                info("findAndMergeSyakenNext ： ".memory_get_usage() / (1024 * 1024) ."MB\n");
            }
        }
             
        \Log::debug('>>>> store target list');
        //\Log::debug($storeTargets);

        // データを抽出
        foreach( $storeTargets as $target ){
            ResultCars::merge( $target );
        }
        
    }

    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeSyaken( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VResultSyaken::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeSyakenNext( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VResultSyakenNext::findTarget( $from, $to );
        
        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeAnkai( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VResultAnkai::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeHoutei12( $from, $to, &$storeTargets ){
        // 対象データを取得
        $targetDatas = VResultHoutei12::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }


    /**
     * @param from
     * @param to
     * @param storeTargets
     */
    private function findAndMergeMuryou6( $from, $to, &$storeTargets ){
        // 対象データを取得s
        $targetDatas = VResultMuryou6::findTarget( $from, $to );

        // 値が空でない時に対象データを追加
        if ( count( $targetDatas ) > 0 ){
            $storeTargets = array_merge( $storeTargets, $targetDatas->toArray() );
        }
    }

}
