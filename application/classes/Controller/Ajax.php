<?php

class Controller_Ajax extends Controller
{
    /** @var Model_CRM */
    private $crmModel;

    /** @var Model_Price */
    private $priceModel;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->crmModel = Model::factory('CRM');
        $this->priceModel = Model::factory('Price');
    }

    private function render($body, $json = true)
    {
        $this->auto_render = false;
        $this->is_ajax = true;
        $this->response->body($body);
    }

    public function action_search_order_spare_offer()
    {
        return $this->render(json_encode($this->crmModel->searchOrderSpareOffer($this->request->post('article'))));
    }

    public function action_search_order_spare()
    {
        return $this->render(json_encode($this->crmModel->findOrderSpare((int)$this->request->post('orderId'))));
    }

    public function action_set_order_spare_by_search()
    {
        return $this->render(json_encode($this->crmModel->setOrderSpareBySearch((int)$this->request->post('id'), (int)$this->request->post('itemId'))));
    }

    public function action_search_order_by_number()
    {
        return $this->render($this->crmModel->findOrderById((int)$this->request->post('id')) ? (int)$this->request->post('id') : 0, false);
    }

    public function action_add_spare_to_order_from_search()
    {
        return $this->render($this->crmModel->addSpareToOrderFromSearch((int)$this->request->post('orderId'), (int)$this->request->post('itemId')), false);
    }

    public function action_remove_spare()
    {
        return $this->render($this->crmModel->removeSpare((int)$this->request->post('spareId')), false);
    }

    public function action_find_vehicles_brand()
    {
        $this->render(json_encode($this->crmModel->findVehicleBrands($this->request->query('query'))));
    }

    public function action_find_vehicles_model()
    {
        $this->render(json_encode($this->crmModel->findVehicleModels($this->request->query('brand'), $this->request->query('query'))));
    }

    public function action_find_cities()
    {
        $this->render(json_encode($this->crmModel->findCitiesByQuery($this->request->query('query'))));
    }

    public function action_find_transport_companies()
    {
        $this->render(json_encode($this->crmModel->findTransportCompaniesByQuery($this->request->query('query'))));
    }

    public function action_clear_suppliers_items()
    {
        $this->render($this->crmModel->clearSuppliersItems((int)$this->request->post('supplierId')), false);
    }

    public function action_search_spare_by_api()
    {
        $updateTask = $this->crmModel->searchSpareByApi($this->request->post('article'));
        $this->render(json_encode($updateTask === null ? [] : $this->crmModel->findSupplierItemByUpdateTask($updateTask)));
    }

    public function action_add_supplier_markup_range()
    {
        $this->render($this->crmModel->addSupplierMarkupRanges($this->request->post('supplierId')), false);
    }

    public function action_delete_item_image()
    {
        $this->render($this->crmModel->deleteItemImage((int)$this->request->post('id')), false);
    }

    public function action_delete_item_usage()
    {
        $this->render($this->crmModel->deleteItemUsage((int)$this->request->post('id')), false);
    }

    public function action_insert_items_tmp()
    {
        $this->render($this->crmModel->insertItemsTmp(), false);
    }

    public function action_export_price_to_farpost()
    {
        $this->render($this->crmModel->exportPriceToFarpost(), false);
    }

    public function action_insert_items_tmp_for_auto_update()
    {
        $this->render((int)$this->priceModel->insertItemsTmpForAutoUpdate((int)$this->request->post('supplierId')));
    }

    public function action_auto_update_supplier_items()
    {
        $this->render($this->priceModel->autoUpdateSupplierItems());
    }
}