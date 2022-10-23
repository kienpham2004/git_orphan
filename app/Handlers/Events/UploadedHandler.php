<?php

namespace App\Handlers\Events;

//use App\Commands\Extract\ExtractTargetCarsCommand;
//use App\Commands\Extract\ExtractResultCarsCommand;

use App\Original\Util\CodeUtil;
use App\Models\UploadHistory;
use App\Events\UploadedEvent;
use Illuminate\Foundation\Bus\DispatchesCommands;

/**
 * アップロードイベント処理のハンドラー
 *
 * @author yhatsutori
 */
class UploadedHandler
{
    use DispatchesCommands;

    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * UploadedEventが発火されたタイミングで動作
     * 各csvアップロード情報を登録
     * 本社管理のアップロード画面の下のほうにある情報のこと
     *
     * @param  UploadedEvent  $event
     * @return void
     */
    public function handle( UploadedEvent $event ){
        // 指定したcsvファイルのDBの総数を取得する
        $modelSum = CodeUtil::getCsvModelCount( $event->file_type );
        
        // アップロード履歴を登録、更新する
        UploadHistory::merge(
            $event->file_type,
            $event->resultCnt,
            $modelSum
        );
        
        // 顧客マスタの取り込みの場合、ロックデータ作成コマンドの実行
        if( $event->file_type == "3" ){
            /*
            $this->dispatch(
                new ExtractTargetCarsCommand()
            );
            */
        }

        // PITデータの取り込みの場合、集計データ作成コマンドの実行
        if( $event->file_type == "5" ){
            /*
            $this->dispatch(
                new ExtractResultCarsCommand()
            );
            */
        }
    }

}
