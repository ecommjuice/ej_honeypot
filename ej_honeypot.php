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
        $this->version = '1.0.0';
        $this->author = 'EJ Agencia';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EJ Honeypot Anti-Spam');
        $this->description = $this->l('Protege tus formularios de bots usando la técnica Honeypot.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayContactForm') &&
            $this->registerHook('displayCustomerAccountForm') &&
            $this->registerHook('actionContactFormSubmitBefore') &&
            $this->registerHook('actionCustomerRegisterSubmitBefore');
    }

    // Insertamos el campo oculto en los formularios
    public function hookDisplayContactForm()
    {
        return $this->display(__FILE__, 'views/templates/hook/honeypot.tpl');
    }

    public function hookDisplayCustomerAccountForm()
    {
        return $this->display(__FILE__, 'views/templates/hook/honeypot.tpl');
    }

    // Validación para el formulario de contacto
    public function hookActionContactFormSubmitBefore()
    {
        $this->validateHoneypot();
    }

    // Validación para el formulario de registro
    public function hookActionCustomerRegisterSubmitBefore()
    {
        $this->validateHoneypot();
    }

    private function validateHoneypot()
    {
        // Si el campo "ej_honeypot_field" tiene contenido, es un bot
        if (Tools::getValue('ej_honeypot_field') && Tools::getValue('ej_honeypot_field') != "") {
            die("Spam detected. Request blocked.");
        }
    }
}
