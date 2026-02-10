<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Ej_Honeypot extends Module
{
    public function __construct()
    {
        $this->name = 'ej_honeypot';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'EJ Agencia';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EJ Honeypot Anti-Spam');
        $this->description = $this->l('Configura protección invisible contra bots en tus formularios.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        // Valores por defecto: todos activados al instalar
        Configuration::updateValue('EJ_HONEYPOT_CONTACT', 1);
        Configuration::updateValue('EJ_HONEYPOT_REGISTER', 1);
        Configuration::updateValue('EJ_HONEYPOT_NEWSLETTER', 1);

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayContactForm') &&
            $this->registerHook('displayCustomerAccountForm') &&
            $this->registerHook('displayNewsletterRegistration') &&
            $this->registerHook('actionContactFormSubmitBefore') &&
            $this->registerHook('actionCustomerRegisterSubmitBefore') &&
            $this->registerHook('actionNewsletterRegistrationBefore');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EJ_HONEYPOT_CONTACT');
        Configuration::deleteByName('EJ_HONEYPOT_REGISTER');
        Configuration::deleteByName('EJ_HONEYPOT_NEWSLETTER');
        return parent::uninstall();
    }

    // --- SECCIÓN DE CONFIGURACIÓN (BACKOFFICE) ---

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('EJ_HONEYPOT_CONTACT', (int)Tools::getValue('EJ_HONEYPOT_CONTACT'));
            Configuration::updateValue('EJ_HONEYPOT_REGISTER', (int)Tools::getValue('EJ_HONEYPOT_REGISTER'));
            Configuration::updateValue('EJ_HONEYPOT_NEWSLETTER', (int)Tools::getValue('EJ_HONEYPOT_NEWSLETTER'));
            $output .= $this->displayConfirmation($this->l('Configuración actualizada correctamente.'));
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuración de Formularios'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Proteger Formulario de Contacto'),
                        'name' => 'EJ_HONEYPOT_CONTACT',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Proteger Registro de Clientes'),
                        'name' => 'EJ_HONEYPOT_REGISTER',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Proteger Suscripción Newsletter'),
                        'name' => 'EJ_HONEYPOT_NEWSLETTER',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Guardar'),
                    'class' => 'btn btn-default pull-right'
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->fields_value['EJ_HONEYPOT_CONTACT'] = Configuration::get('EJ_HONEYPOT_CONTACT');
        $helper->fields_value['EJ_HONEYPOT_REGISTER'] = Configuration::get('EJ_HONEYPOT_REGISTER');
        $helper->fields_value['EJ_HONEYPOT_NEWSLETTER'] = Configuration::get('EJ_HONEYPOT_NEWSLETTER');

        return $helper->generateForm([$fields_form]);
    }

    // --- LÓGICA DE FILTRADO ---

    public function hookDisplayContactForm()
    {
        if (Configuration::get('EJ_HONEYPOT_CONTACT')) {
            return $this->display(__FILE__, 'views/templates/hook/honeypot.tpl');
        }
    }

    public function hookDisplayCustomerAccountForm()
    {
        if (Configuration::get('EJ_HONEYPOT_REGISTER')) {
            return $this->display(__FILE__, 'views/templates/hook/honeypot.tpl');
        }
    }

    public function hookDisplayNewsletterRegistration()
    {
        if (Configuration::get('EJ_HONEYPOT_NEWSLETTER')) {
            return $this->display(__FILE__, 'views/templates/hook/honeypot.tpl');
        }
    }

    public function hookActionContactFormSubmitBefore()
    {
        if (Configuration::get('EJ_HONEYPOT_CONTACT')) { $this->validateHoneypot(); }
    }

    public function hookActionCustomerRegisterSubmitBefore()
    {
        if (Configuration::get('EJ_HONEYPOT_REGISTER')) { $this->validateHoneypot(); }
    }

    public function hookActionNewsletterRegistrationBefore()
    {
        if (Configuration::get('EJ_HONEYPOT_NEWSLETTER')) { $this->validateHoneypot(); }
    }

    private function validateHoneypot()
    {
        if (Tools::getValue('ej_honeypot_field') && Tools::getValue('ej_honeypot_field') != "") {
            // Log opcional: podrías registrar aquí el ataque
            die("Spam protection triggered.");
        }
    }
}
