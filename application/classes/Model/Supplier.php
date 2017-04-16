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
}