<?php

/**
 * Class Model_Supplier
 */
class Model_Supplier extends Kohana_Model
{
    /**
     * @return array
     */
    public function getSuppliersList()
    {
        return DB::select('ss.*',
            [
                DB::select(DB::expr('COUNT(si.id)'))
                    ->from(['suppliers__items', 'si'])
                    ->where('si.supplier_id', '=', DB::expr('ss.id'))
                    ->and_where('si.quantity', '!=', 0)
                    ->and_where('si.price', '!=', 0),
                'price_count'])
            ->from(['suppliers__suppliers', 'ss'])
            ->execute()
            ->as_array()
            ;
    }

    /**
     * @param $supplierId
     * @return false|array
     */
    public function findSupplierById($supplierId)
    {
        return DB::select()
            ->from('suppliers__suppliers')
            ->where('id', '=', $supplierId)
            ->limit(1)
            ->execute()
            ->current()
            ;
    }

    /**
     * @param $name
     */
    public function addSupplier($name)
    {
        DB::insert('suppliers__suppliers', ['name'])
            ->values([$name])
            ->execute()
        ;
    }

    /**
     * @param $supplierId
     * @return false|array
     */
    public function getSupplierMarkup($supplierId)
    {
        return DB::select()
            ->from('suppliers__markups')
            ->limit(1)
            ->where('supplier_id', '=', $supplierId)
            ->execute()
            ->current()
            ;
    }
}