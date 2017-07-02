<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Crm extends Controller
{
    /** @var Model_CRM */
    private $crmModel;

    /** @var  Model_Supplier */
    private $supplierModel;

    /** @var  Model_Price */
    private $priceModel;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        if (!Auth::instance()->logged_in('admin') && $request->uri() !== 'crm/login') {
            HTTP::redirect('/crm/login');
        }

        $this->crmModel = Model::factory('CRM');
        $this->supplierModel = Model::factory('Supplier');
        $this->priceModel = Model::factory('Price');
    }

    public function action_index()
	{
		$this->response->body(View::factory('crm/template')->set('content', ''));
	}

    public function action_login()
    {
        if (!Auth::instance()->logged_in() && isset($_POST['login'])) {
            Auth::instance()->login($this->request->post('username'), $this->request->post('password'),true);
            HTTP::redirect('/crm/new_order');
        }

        $template = View::factory('crm/login')
            ->set('post', $this->request->post())
        ;

        $this->response->body($template);
    }

    public function action_logout()
    {
        if (Auth::instance()->logged_in() && isset($_POST['logout'])) {
            Auth::instance()->logout();

            HTTP::redirect('/crm');
        }
    }

	public function action_new_order()
	{
	    if ((int)$this->request->post('newOrder') === 1) {
	        $orderId = $this->crmModel->addOrder($this->request->post());

            HTTP::redirect('/crm/order/' . $orderId);
        }

	    $content = View::factory('crm/new_order')
            ->set('order', $this->crmModel->findOrderById((int)$this->request->query('copy')))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}

	public function action_order()
	{
	    $orderId = (int)$this->request->param('id');

	    if ((int)$this->request->post('redactOrder') === 1) {
	        $this->crmModel->setOrder($this->request->post(), $orderId);

            HTTP::redirect('/crm/order/' . $orderId);
        }

	    if ((int)$this->request->post('redactOrderSpares') === 1) {
	        $this->crmModel->setOrderSpares($this->request->post(), $orderId);

            HTTP::redirect('/crm/order/' . $orderId);
        }

	    if ((int)$this->request->post('addSpare') === 1) {
	        $this->crmModel->addEmptySpare($orderId);

            HTTP::redirect('/crm/order/' . $orderId);
        }

	    $content = View::factory('crm/order')
            ->set('order', $this->crmModel->findOrderById($orderId))
            ->set('orderSpares', $this->crmModel->findOrderSpares($orderId))
            ->set('orderStatusesList', $this->crmModel->getOrderStatusesList())
            ->set('paymentStatusesList', $this->crmModel->getPaymentStatusesList())
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}


	public function action_invoice()
	{
	    $orderId = (int)$this->request->param('id');

	    $content = View::factory('crm/order_invoice')
            ->set('order', $this->crmModel->findOrderById($orderId))
            ->set('orderSpares', $this->crmModel->findOrderSpares($orderId))
        ;

        $this->response->body($content);
	}

	public function action_orders_list()
	{
	    $firstDate = new DateTime($this->request->query('first_date'));
        $lastDate = new DateTime($this->request->query('last_date'));

        $content = View::factory('crm/orders_list')
            ->set('first_date', $firstDate->format('d.m.Y'))
            ->set('last_date', $lastDate->format('d.m.Y'))
            ->set('ordersList', $this->crmModel->getOrdersList($firstDate, $lastDate))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}

	public function action_search_orders()
	{
        $content = View::factory('crm/search_orders')
            ->set('query', $this->request->query())
            ->set('ordersList', $this->crmModel->searchOrders($this->request->query()))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}

	public function action_search_items()
	{
        $content = View::factory('crm/search_items')
            ->set('orderId', $this->request->query('order_id'))
            ->set('article', $this->request->query('article'))
            ->set('itemsList', $this->crmModel->searchOrderSpareOffer($this->request->query('article')))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}

	public function action_suppliers_list()
	{
	    if ((int)$this->request->post('addSupplier') === 1) {
	        $this->supplierModel->addSupplier($this->request->post('name'));

            HTTP::redirect($this->request->referrer());
        }

        if ((int)$this->request->post('exportPriceToFarpost') === 1) {
            $this->crmModel->exportPriceToFarpost();

            HTTP::redirect($this->request->referrer());
        }

        $filename=Arr::get($_FILES, 'priceName', []);

        if (!empty($filename)) {
            $this->priceModel->manualUpdateSupplierItems($_FILES, (int)$this->request->post('supplierId'));

            HTTP::redirect($this->request->referrer());
        }

        $content = View::factory('crm/suppliers_list')
            ->set('suppliersList', $this->supplierModel->getSuppliersList())
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
	}

    public function action_registration()
    {
        $template = View::factory('crm/registration')
            ->set('post', $this->request->post())
            ->set('error', '')
        ;

        if (count($this->request->post())) {
            if (empty(Arr::get($_POST,'username'))) {
                $template->set('error', '<div class="alert alert-danger"><strong>Не указан логин!</strong> Укажите Ваш логин.</div>');
            } elseif (empty(Arr::get($_POST,'email'))) {
                $template->set('error', '<div class="alert alert-danger"><strong>Не указана почта!</strong> Укажите Вашу почту.</div>');
            } elseif (Arr::get($_POST,'password','')=="") {
                $template->set('error', '<div class="alert alert-danger"><strong>Не указан пароль!</strong> Укажите Ваш пароль.</div>');
            } else if (Arr::get($_POST,'password') != Arr::get($_POST,'password2')) {
                $template->set('error', '<div class="alert alert-danger"><strong>Пароли не совпадают!</strong> Проверьте правильность подтверждения пароля.</div>');
            } else {
                $user = ORM::factory('User');
                $user->values(array(
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'password_confirm' => $_POST['password2'],
                ));
                $some_error = false;

                try {
                    $user->save();
                    $user->add("roles",ORM::factory("Role",1));
                }
                catch (ORM_Validation_Exception $e) {
                    $some_error = $e->errors('models');
                }

                if ($some_error) {
                    $template->set('error', '<div class="alert alert-danger"><strong>Ошибка регистрационных данных!</strong> Проверьте правильность ввода данных.</div>');

                    if (isset($some_error['username'])) {
                        if ($some_error['username'] == "models/user.username.unique") {
                            $template->set('error', '<div class="alert alert-danger"><strong>Такой логин уже есть в базе!</strong> Придумайте новый логин.</div>');
                        }
                    }
                    else if (isset($some_error['email'])) {
                        if ($some_error['email']=="email address must be an email address") {
                            $template->set('error', '<div class="alert alert-danger"><strong>Некорректный формат почты!</strong> Проверьте правильность написания почты.</div>');
                        }
                        if ($some_error['email']=="models/user.email.unique") {
                            $template->set('error', '<div class="alert alert-danger"><strong>Такая почта есть в базе!</strong> Укажите другую почту.</div>');
                        }
                    }
                } else {
                    HTTP::redirect('/crm');
                }
            }
        }

        $this->response->body($template);
    }

    public function action_supplier_markup()
    {
        if ((int)$this->request->post('redactMarkup') === 1) {
            $this->crmModel->setSupplierMarkups($this->request->param('id'), $this->request->post());
        }

        $content = View::factory('crm/supplier_markup')
            ->set('supplier', $this->supplierModel->findSupplierById($this->request->param('id')))
            ->set('supplierMarkup', $this->supplierModel->getSupplierMarkup($this->request->param('id')))
            ->set('supplierMarkupRanges', $this->crmModel->findSupplierMarkupRangesBySupplier($this->request->param('id')))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
    }

    public function action_crosses()
    {
        $filename=Arr::get($_FILES, 'crosses', []);

        if (!empty($filename)) {
            $this->crmModel->loadCrosses($_FILES);

            HTTP::redirect($this->request->referrer());
        }

        $content = View::factory('crm/crosses')
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
    }



    public function action_items_images()
    {
        if ((int)$this->request->post('loadImage') === 1) {
            $this->crmModel->loadImage($this->request->post('brand'), $this->crmModel->getSearchArticle($this->request->post('article')), $_FILES, $this->request->post('outer_link'), true);

            HTTP::redirect($this->request->referrer());
        }

        if (!empty($_FILES['imagesPackage'])) {
            $this->crmModel->loadImagesPackage($_FILES['imagesPackage']);

            HTTP::redirect($this->request->referrer());
        }

        $content = View::factory('crm/items_images')
            ->set('imagesList', $this->crmModel->getAllItemsImages(
                ((Arr::get($_GET, 'page', 1) - 1) * $this->crmModel->defaultLimit),
                Arr::get($_GET, 'limit', $this->crmModel->defaultLimit),
                $this->request->query('brand'),
                $this->request->query('article')
            ))
            ->set('imagesCount', $this->crmModel->getCountAllItemsImages(
                $this->request->query('brand'),
                $this->request->query('article')
            ))
            ->set('brand', $this->request->query('brand'))
            ->set('article', $this->request->query('article'))
            ->set('page', (int)Arr::get($_GET, 'page', 1))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
    }

    public function action_items_usages()
    {
        if (!empty($_FILES['usagesPackage'])) {
            $this->crmModel->loadUsagesPackage($_FILES['usagesPackage']);

            HTTP::redirect($this->request->referrer());
        }

        $content = View::factory('crm/items_usages')
            ->set('usagesList', $this->crmModel->getAllItemsUsages(
                ((Arr::get($_GET, 'page', 1) - 1) * $this->crmModel->defaultLimit),
                (int)Arr::get($_GET, 'limit', $this->crmModel->defaultLimit),
                $this->request->query('brand'),
                $this->request->query('article')
            ))
            ->set('usagesCount', $this->crmModel->getCountAllItemsUsages(
                $this->request->query('brand'),
                $this->request->query('article')
            ))
            ->set('brand', $this->request->query('brand'))
            ->set('article', $this->request->query('article'))
            ->set('page', (int)Arr::get($_GET, 'page', 1))
        ;

        $this->response->body(View::factory('crm/template')->set('content', $content));
    }
}