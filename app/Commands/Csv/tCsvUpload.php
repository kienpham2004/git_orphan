<?php

namespace App\Commands\Csv;

use App\Lib\Csv\CsvImport;
use App\Original\CsvData\CustomerData;
use App\Original\CsvData\CustomerUmuData;
use App\Original\CsvData\TmrData;
use App\Original\CsvData\PitData;
use App\Original\CsvData\AbcData;
use App\Original\CsvData\CiaoData;
use App\Original\CsvData\SmartProData;
use App\Original\CsvData\ContactData;
use App\Original\CsvData\IkouData;
use App\Original\CsvData\RecallData;
use App\Original\CsvData\ContactCommentData;
use App\Original\CsvData\InsuranceData;
use App\Original\CsvData\DaigaeSyakenData;
use App\Original\CsvData\SyakenJisshiData;
use App\Original\CsvData\HtcData;
use Input;
use DB;

/**
 * csvアップロード処理のトレイト
 *
 * @author yhatsutori
 */
trait tCsvUpload{
    
    /**
     * CSVの取り込みを行うオブジェクトを取得
     * @param  [type] $file_type [description]
     * @param  [type] $filePath  [description]
     * @return [type]            [description]
     */
    public function getInstance( $file_type,  $filePath, $contact = false ){
        // 顧客データ file_type::3
        if( $file_type == "3" ){
            
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new CustomerData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );
            
            return $csvImportObj;
        }

        // TMR file_type::4
        if( $file_type == "4" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new TmrData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                2 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // PIT管理 file_type::5
        if( $file_type == "5" ){
            
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new PitData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // ABC file_type::6
        if( $file_type == "6" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new AbcData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );
            
            return $csvImportObj;
        }

        // チャオデータ file_type::7
        if( $file_type == "7" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new CiaoData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                2 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // 活動日報実績 file_type::8
        if( $file_type == "8" ){
            // 活動日報実績 file_type::8
            if(!$contact){
                // csvを取り込む
                $csvImportObj = CsvImport::getInstance(
                    new ContactData(),
                    $filePath, // ファイルパス
                    Input::all(), // 検索条件を取得
                    1  // 読み飛ばす列数
                );

                return $csvImportObj;
            } else {
                // csvを取り込む
                $csvImportObj = CsvImport::getInstance(
                    new ContactCommentData(),
                    $filePath, // ファイルパス
                    Input::all(), // 検索条件を取得
                    1  // 読み飛ばす列数
                );

                return $csvImportObj;
            }
        }

        // スマートプロ査定 file_type::9
        if( $file_type == "9" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new SmartProData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );
            
            return $csvImportObj;
        }
        
        // 意向確認 file_type::10
        if( $file_type == "10" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new IkouData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                2 // 読み飛ばす列数
            );
            
            return $csvImportObj;
        }

        // リコール file_type::12
        if( $file_type == "12" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new RecallData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // HTCログイン file_type::13
        if( $file_type == "13" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new HtcData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                2 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // 車検実施リスト file_type::14
        if( $file_type == "14" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new SyakenJisshiData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

        // 代替車検推進管理 file_type::15
        if( $file_type == "15" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new DaigaeSyakenData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );
            return $csvImportObj;
          }

        // 保険データ file_type::20
        if( $file_type == "20" ){
            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new InsuranceData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                3 // 読み飛ばす列数
            );

            return $csvImportObj;
        }

    }

    /**
     * CSVの取り込みを行うオブジェクトを取得
     * @param  [type] $file_type [description]
     * @param  [type] $filePath  [description]
     * @return [type]            [description]
     */
    public function getInstanceUmu( $file_type,  $filePath ){
            
        // 顧客データ file_type::3
        if( $file_type == "3" ){
            // 顧客データの有無を確認するテーブルを初期化
//          $customerUmusql = "  DELETE FROM tb_customer_umu ";

//          DB::select( $customerUmusql );
            
            // シーケンスを初期化
//          $customerUmusql2 = " SELECT setval('tb_customer_umu_id_seq', 1) ";

//          DB::select( $customerUmusql2 );
            DB::table("tb_customer_umu")->truncate();

            // csvを取り込む
            $csvImportObj = CsvImport::getInstance(
                new CustomerUmuData(),
                $filePath, // ファイルパス
                Input::all(), // 検索条件を取得
                1 // 読み飛ばす列数
            );
            
            return $csvImportObj;
        }

    }

}
