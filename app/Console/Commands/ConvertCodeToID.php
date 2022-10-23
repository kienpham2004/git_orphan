<?php

namespace App\Console\Commands;

use App\Original\Util\CodeUtil;
use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ConvertCodeToID extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch:ConvertCodeToID';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '拠点コードと担当者コードから拠点IDと担当者IDを変換する。';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', -1);
        $fromTableBase = 'tb_base';
        $fromTableUser = 'tb_user_account';

        $this->comment(date('Y-m-d H:i:s') ." - Data convert started");

        $this->dataConvert(  'tb_contact', 'ctc_user_id', 'ctc_user_code_init', 'ctc_user_id', $fromTableUser );
        $this->dataConvert(  'tb_contact_mitsumori', 'ctcmi_user_id', 'ctcmi_user_code_init', 'ctcmi_user_id', $fromTableUser );
        $this->dataConvert(  'tb_contact_satei', 'ctcsa_user_id', 'ctcsa_user_code_init', 'ctcsa_user_id', $fromTableUser );
        $this->dataConvert(  'tb_contact_shijo', 'ctcshi_user_id', 'ctcshi_user_code_init', 'ctcshi_user_id', $fromTableUser );
        $this->dataConvert(  'tb_contact_shodan', 'ctcsho_user_id', 'ctcsho_user_code_init', 'ctcsho_user_id', $fromTableUser );
        $this->dataConvert(  'tb_customer', 'user_id', 'user_code_init', 'user_id', $fromTableUser );
        $this->dataConvert(  'tb_customer_umu', 'umu_user_id', 'umu_user_code_init', 'umu_user_id', $fromTableUser );

        $this->dataConvert(  'tb_ikou', 'ik_user_id', 'ik_user_code_init', 'ik_user_id', $fromTableUser );
        $this->dataConvert(  'tb_ikou', 'ik_user_name', 'ik_user_name_csv', '');
        $this->dataConvert(  'tb_ikou', 'ik_base_name', 'ik_base_name_csv', '');

        $this->dataConvert(  'tb_plan', 'plan_user_id', 'plan_user_code_old', 'plan_user_id', $fromTableUser );

        $this->dataConvert(  'tb_result', 'rst_user_id', 'rst_user_code_init', 'rst_user_id', $fromTableUser );
        $this->dataConvert(  'tb_result', 'rst_user_name', 'rst_user_name_csv', '' );

        $this->dataConvert(  'tb_result_cars', 'rstc_user_id', 'rstc_user_code_init', 'rstc_user_id', $fromTableUser );
        $this->dataConvert(  'tb_result_cars', 'rstc_user_name', 'rstc_user_name_csv', '');

        $this->dataConvert(  'tb_smart_pro', 'smart_user_id', 'smart_user_code_init', 'smart_user_id', $fromTableUser );
        $this->dataConvert(  'tb_smart_pro', 'smart_user_name', 'smart_user_name_csv', '');

        $this->dataConvert(  'tb_target_cars', 'tgc_user_id', 'tgc_user_code_init', 'tgc_user_id', $fromTableUser );
        $this->dataConvert(  'tb_target_cars', 'tgc_base_name', 'tgc_base_name_csv', '');
        $this->dataConvert(  'tb_target_cars', 'tgc_user_name', 'tgc_user_name_csv', '');

        $this->dataConvert(  'tb_tmr', 'tmr_user_id', 'tmr_user_code_init', 'tmr_user_id', $fromTableUser );
        $this->dataConvert(  'tb_insurance', 'insu_user_id', 'insu_user_code_init', 'insu_user_id', $fromTableUser );
        $this->dataConvert( 'tb_manage_info', '', '', 'mi_user_id', $fromTableUser );


        $this->dataConvert(  $fromTableUser, 'base_code', 'base_code_old', 'base_id', $fromTableBase );
        $this->dataConvert(  'tb_customer', 'base_code', 'base_code_init', '', $fromTableBase );
        $this->dataConvert( 'tb_manage_info', 'mi_base_code', 'mi_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_customer_umu', 'umu_base_code', 'umu_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_ikou', 'ik_base_code', 'ik_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_plan', 'plan_base_code', 'plan_base_code_old', 'plan_base_id', $fromTableBase );
        $this->dataConvert(  'tb_result', 'rst_base_code', 'rst_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_result_cars', 'rstc_base_code', 'rstc_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_smart_pro', 'smart_base_code', 'smart_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_target_cars', 'tgc_base_code', 'tgc_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_tmr', 'tmr_base_code', 'tmr_base_code_init', '', $fromTableBase );
        $this->dataConvert(  'tb_insurance', 'insu_base_code', 'insu_base_code_init', '', $fromTableBase );

        $this->dataConvert( 'tb_customer', 'base_name', 'base_name_csv', '' );
        $this->dataConvert( 'tb_customer', 'user_name', 'user_name_csv', '' );
        $this->dataConvert( 'tb_insurance', 'insu_base_name', 'insu_base_name_csv', '' );
        $this->dataConvert( 'tb_insurance', 'insu_user_name', 'insu_user_name_csv', '' );

        $this->dataConvert( 'tb_info', 'info_user_id', 'info_user_code_old', 'info_user_id', $fromTableUser );
        $this->dataConvert( 'tb_info', 'info_base_code', 'info_base_code_old', '' );

        $this->comment(date('Y-m-d H:i:s') ." - Data convert ended");
    }

    /**
     * @param $tbName
     * @param $fieldOld
     * @param $fieldNew
     * @param $fieldAdd
     * @param string $fromTable
     */
    private function dataConvert($tbName, $fieldOld, $fieldNew, $fieldAdd, $fromTable = "")
    {
        $this->comment('convert starting!!!!');
        // テーブルの項目を存在するチェックと項目作成
        $this->comment('table: ' . $tbName . '. Check old field exists: ' . $fieldOld);
        $checkOldField = $this->checkExitsField($tbName, $fieldOld);
        $checkNewField = $this->checkExitsField($tbName, $fieldNew);

        if ( $fieldOld === '' || $fieldNew === '' ) {
            $checkFieldAdd = $this->checkExitsField($tbName, $fieldAdd);
            if ( !$checkFieldAdd ) {
                $this->addColumnName($tbName, $fieldAdd);
            }
        }

        //存在している場合は、コラム名を編集
        if ( $checkOldField && !$checkNewField ) {
            $this->comment('table: ' . $tbName . '. Old field existed: ' . $fieldOld);
            $this->editColumnName($tbName, $fieldOld, $fieldNew);
            $this->comment('Editted. Old filed: ' . $fieldOld . ' New field: ' . $fieldNew);
        }

        $this->comment('table: ' . $tbName . '. Check adding field exists: ' . $fieldAdd);
        $checkFieldAdd = $this->checkExitsField($tbName, $fieldAdd);

        //追加しようとするコラムが存在しない場合、追加を行う。
        if ( !$checkFieldAdd ) {
            $this->comment('table: ' . $tbName . '. Adding field not existed: ' . $fieldOld);
            $this->addColumnName($tbName, $fieldAdd);
            $this->comment('Field added: ' . $fieldAdd);
        }

        $checkUserIdExists = $this->checkExitsField('tb_user_account', 'user_id');

        //tb_user_accountのuser_idをuser_codeに変更する
        if ( $checkUserIdExists ) {
            $this->comment('user id existed!!');
            $this->editUserIdColumnName();
            $this->comment('user id edited!!');
        }

        //更新しようとするコラムが存在しない場合はデータ更新を行う。その以外は行わない。
        if ( !$checkNewField ) {
            $this->updateData($tbName, $fieldNew, $fieldAdd, $fromTable);
        }
        $this->comment('convert finished!!!!');
    }

    /**
     * コラムの存在チェックを行う
     * @param $tableName　テーブル名
     * @param $fieldName　項目名
     * @return bool True|False
     */
    private function checkExitsField($tableName, $fieldName)
    {
        //$fieldNameは何にも入っていない場合は、何にも実行しないように。
        if ( $fieldName === '' ) {
            return true;
        }
        //Schemaにより、コラムがテーブルに存在しているかチェック。
        $isExists = Schema::hasColumn($tableName, $fieldName);

        return $isExists;
    }

    /**
     * コラム名を編集
     * @param $tableName
     * @param $fieldNameOld　旧項目
     * @param $fieldNameNew　新項目
     */
    private function editColumnName($tableName, $fieldNameOld, $fieldNameNew)
    {
        Schema::table($tableName, function(Blueprint $table) use ( $fieldNameOld, $fieldNameNew ){
            $table->renameColumn($fieldNameOld, $fieldNameNew);
        });
    }

    /**
     * user_idをuser_codeに編集
     * @param string $tableName
     * @param string $oldColumn
     * @param string $newColumn
     */
    private function editUserIdColumnName($tableName='tb_user_account', $oldColumn='user_id', $newColumn='user_code')
    {
        Schema::table($tableName, function(Blueprint $table) use ( $oldColumn, $newColumn ) {
            $table->renameColumn($oldColumn, $newColumn);
        });
    }

    /**
     * $tableNameにより新コラムを作成
     * @param $tableName
     * @param $fieldName
     */
    private function addColumnName($tableName, $fieldName)
    {
        Schema::table($tableName, function(Blueprint $table) use ( $fieldName) {
            $table->integer($fieldName)->nullable();
        });
    }


    /**
     * データを更新
     * @param $tableName
     * @param $fieldToSelect
     * @param $fieldToSet
     * @param $fromTable
     */
    private function updateData($tableName, $fieldToSelect, $fieldToSet, $fromTable)
    {
        $this->comment('update data starting!!!');
        //$fieldToSetはnullじゃない場合は実行する
        if ( $fieldToSet !== '' ) {
            $this->comment('table name: ' . $tableName . '. field to select: ' . $fieldToSelect . '. Field to set: ' . $fieldToSet . '. From table: ' . $fromTable);
            if ( $fromTable === 'tb_base' ) {
                $this->comment('starting update with tb_base');
                $updateSql = "UPDATE {$tableName} SET {$fieldToSet} = {$fromTable}.id 
                      FROM {$fromTable} WHERE {$fromTable}.base_code = {$tableName}.{$fieldToSelect}";
                $this->comment('finished update with tb_base');
            }
            else {
                $this->comment('starting update with tb_user_account');
                $updateSql = "UPDATE {$tableName} SET {$fieldToSet} = {$fromTable}.id 
                      FROM {$fromTable} WHERE {$fromTable}.user_code = {$tableName}.{$fieldToSelect}";
                $this->comment('finished update with tb_user_account');
            }

            DB::statement($updateSql);
        }
        //何にも入ってない場合は実行しない。
        else {
            $this->comment('field to set is null. No updating');
        }
        $this->comment('update data finished!!!');
    }

    /**
     * collectの形から
     * [$colKey => $colVal]のような形のarrayを作成
     * @param $table
     * @param $colKey
     * @param $colVal
     * @return array
     */
    public function pluck($table, $colKey, $colVal)
    {
        $collect = collect(DB::table($table)->select($colKey, $colVal)->groupBy('id')->get())->toArray();
        $pluck = [];
        foreach ($collect as $v) {
            $pluck[$v->$colKey] = $v->$colVal;
        }
        return $pluck;
    }
}
