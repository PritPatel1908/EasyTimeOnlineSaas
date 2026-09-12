<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

trait HasDynamicTable
{
    public function initializeHasDynamicTable()
    {
        // $this->setDynamicTable(session('current_year', Carbon::now()->year));
    }

    public function setDynamicTable($year = null)
    {
        if (is_null($year)) {
            $year = Carbon::now()->year;
            $tablename = $this->getDynamicTableName(session('current_year', $year));
        } else {
            $tablename = $this->getDynamicTableName($year);
        }

        if (! Schema::hasTable($tablename)) {
            $this->createDynamicTable($tablename, $year);
        }
        $this->setTable($tablename);
    }

    protected function getDynamicTableName($year)
    {
        return $this->baseTableName().$year;
    }

    public function getYearFromTableName()
    {
        $tableName = $this->getTable();
        $year = substr($tableName, strlen($this->baseTableName()));

        return $year;
    }

    protected function createDynamicTable($tablename, $year)
    {
        if (! Schema::hasTable($tablename)) {
            Schema::create($tablename, function (Blueprint $table) use ($year) {
                $this->defineSchema($table, $year);
            });
        }
    }

    public function scopeTableYear($query, $year)
    {
        $tableName = $this->getDynamicTableName($year);
        $this->setDynamicTable($year);

        return $query->from($tableName);
    }

    abstract protected function baseTableName();

    abstract protected function defineSchema(Blueprint $table, $year);
}
