<?php

class Controller_Ajax extends Controller
{
    /** @var Model_CRM */
    private $crmModel;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        $this->crmModel = Model::factory('CRM');
    }

    public function action_search_order_spare_offer()
    {
        return $this->response->body(json_encode($this->crmModel->searchOrderSpareOffer($this->request->post('article'))));
    }

    public function action_search_order_spare()
    {
        return $this->response->body(json_encode($this->crmModel->findOrderSpare((int)$this->request->post('orderId'))));
    }

    public function action_set_order_spare_by_search()
    {
        return $this->response->body(json_encode($this->crmModel->setOrderSpareBySearch((int)$this->request->post('id'), (int)$this->request->post('itemId'))));
    }

    public function action_search_order_by_number()
    {
        return $this->response->body($this->crmModel->findOrderById((int)$this->request->post('id')) ? (int)$this->request->post('id') : 0);
    }

    public function action_add_spare_to_order_from_search()
    {
        return $this->response->body($this->crmModel->addSpareToOrderFromSearch((int)$this->request->post('orderId'), (int)$this->request->post('itemId')));
    }

    public function action_remove_spare()
    {
        return $this->response->body($this->crmModel->removeSpare((int)$this->request->post('spareId')));
    }

    public function action_find_vehicles_brand()
    {
        $this->response->body(json_encode($this->crmModel->findVehicleBrands($this->request->query('query'))));
    }

    public function action_find_vehicles_model()
    {
        $this->response->body(json_encode($this->crmModel->findVehicleModels($this->request->query('brand'), $this->request->query('query'))));
    }

    public function action_find_city()
    {
        $this->response->body(json_encode($this->crmModel->findCitiesByQuery($this->request->query('query'))));
    }
}