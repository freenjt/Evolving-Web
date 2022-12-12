<?php

namespace Drupal\ab_cdp_connector_global\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure CDP Connector Global settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ab_cdp_connector_global_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ab_cdp_connector_global.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['site_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site domain'),
      '#required' => TRUE,
      '#default_value' => $this->config('ab_cdp_connector_global.settings')->get('site_domain'),
    ];
    $form_ids = $form_state->getValue('form_ids') ?? $this->config('ab_cdp_connector_global.settings')->get('form_ids') ?? [];

    $form['form_ids'] = [
      '#type' => 'value',
      '#value' => $form_ids,
    ];

    $form['cdp_containers'] = [
      '#prefix' => '<div id="cdp-containers">',
      '#suffix' => '</div>',
    ];
    $fields_cdp = $this->getFieldsCDP();

    foreach ($form_ids as $form_id){
      $form['cdp_containers']["cdp_{$form_id}"]=[
        '#type' => 'fieldset',
        '#title' => $this->t('Configuration @form_id CDP', ['@form_id' => $form_id]),
        '#collapsible' => TRUE,
        '#tree' => TRUE,
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['active_cdp'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Active CDP to this form'),
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['active_cdp'] ?? 0,
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['custom_endpoint'] = [
        '#type' => 'textfield',
        '#size' => 120,
        '#title' => $this->t('Custom endpoint'),
        '#description' => $this->t('If you need a different cdp endpoint, insert the string here. You can use the following tokens: [cdp-config:country] [cdp-config:zone]'),
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['custom_endpoint'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['pre_phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country prefix phone'),
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['pre_phone'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['brand'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Brand'),
        '#required' => TRUE,
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['brand'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['campaign'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Campaign'),
        '#required' => TRUE,
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['campaign'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['form'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Form'),
        '#required' => TRUE,
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['form'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['country'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country'),
        '#required' => TRUE,
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['country'] ?? '',
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['unify'] = [
        '#type' => 'select',
        '#title' => $this->t('Unify'),
        '#options' => [
          true => $this->t('True'),
          false => $this->t('False'),
        ],
        '#required' => TRUE,
        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['unify'] ?? true,
      ];
//      $form['cdp_containers']["cdp_{$form_id}"]['zone'] = [
//        '#type' => 'textfield',
//        '#title' => $this->t('Zone'),
//        '#required' => TRUE,
//        '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['zone'],
//      ];
      $form['cdp_containers']["cdp_{$form_id}"]['fields'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Fields CDP'),
        '#prefix' => $this->t('To concatenate two fields, add + sign, eg: field1+field2. Only to scalars values'),
        '#attributes' => [
          'class' => ['container-inline'],
        ],
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
      ];
      foreach($fields_cdp as $key => $field){
        $form['cdp_containers']["cdp_{$form_id}"]['fields'][$key] = [
          '#type' => 'textfield',
          '#title' => 'Abi '.$field,
          '#default_value' => $this->config('ab_cdp_connector_global.settings')->get("cdp_{$form_id}")['fields'][$key] ?? null,
        ];
      }
      $placeholder = '{"Texto":{"Texto":{"Texto":["$variable"]}}}';
      $form['cdp_containers']["cdp_{$form_id}"]['fields']['abi_interest'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Abi Interest'),
        '#attributes' => ['placeholder' => $placeholder],
        '#description' => $this->t('Esta area de texto acepta formato json, para poner una variable, ponga el simbolo $'),
        '#default_value' => $this->config('ab_cdp_connector_global.settings')
            ->get("cdp_{$form_id}")['fields']['abi_interest'] ?? NULL,
      ];
      $form['cdp_containers']["cdp_{$form_id}"]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove CDP'),
        '#submit' => ['::removeCDPSubmit'],
        '#name' => "remove_{$form_id}",
        '#ajax' => [
          'callback' => '::addCDP',
          'wrapper' => 'cdp-containers',
        ],
      ];
    }
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline']
      ],
    ];
    $form['container']['cdp_form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form ID'),
      '#placeholder' => $this->t('eg. user_register_form'),
    ];
    $form['container']['add_cdp'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add CDP'),
      '#submit' => ['::addCDPSubmit'],
      '#ajax' => [
        'callback' => '::addCDP',
        'wrapper' => 'cdp-containers',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if ($form_state->getValue('example') != 'example') {
//      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
//    }
//    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = $form_state->getValues();

    unset($values['cdp_form_id']);
    $cdp_config = $this->config('ab_cdp_connector_global.settings');
    foreach ($values as $key => $value){
      $cdp_config->set($key, $value);
    }
    $cdp_config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Add CDP Form
   */
  public function addCDPSubmit(array &$form, FormStateInterface $form_state){
    $form_id = strtolower($form_state->getValue('cdp_form_id'));
    $form_ids = $form_state->getValue('form_ids');
    $form_ids[] = str_replace('-', '_', $form_id);
    $form_state->setValue('form_ids', $form_ids);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Remove CDP Form
   */
  public function removeCDPSubmit(array &$form, FormStateInterface $form_state){
    $trigger = $form_state->getTriggeringElement()['#name'];
    $form_id = str_replace('remove_', '', $trigger);
    $form_ids = $form_state->getValue('form_ids');
    if (($key = array_search($form_id, $form_ids)) !== false) {
      unset($form_ids[$key]);
    }
    $form_state->setValue('form_ids', $form_ids);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Ajax callback to add CDP container
   */
  public function addCDP(array &$form, FormStateInterface $form_state) {
    return $form['cdp_containers'];
  }

  private function getFieldsCDP(){
    $fields = [
      'abi_name' => $this->t('Full name'),
      'abi_firstname' => $this->t('First name'),
      'abi_lastname' => $this->t('Last name'),
      'abi_cpf' => $this->t('CPF (Identification)'),
      'abi_gender' => $this->t('Gender'),
      'abi_age' => $this->t('Age'),
      'abi_phone' => $this->t('Phone'),
      'abi_email' => $this->t('Email'),
      'abi_dateofbirth' => $this->t('Date of birth'),
      'abi_dayofbirth' => $this->t('Day of birth'),
      'abi_monthofbirth' => $this->t('Month of birth'),
      'abi_yearofbirth' => $this->t('Year of birth'),
      'abi_country' => $this->t('Country'),
      'abi_city' => $this->t('City'),
      'abi_state' => $this->t('State'),
      'abi_zipcode' => $this->t('Zip code'),
      'abi_district' => $this->t('District'),
      'abi_address' => $this->t('Address'),
      'abi_number' => $this->t('Number'),
      'abi_complement' => $this->t('Address complement'),
      'abi_neighborhood' => $this->t('Neighborhood'),
      'abi_soccerteam' => $this->t('Soccer team'),
      'purpose_name' => $this->t('Marketing activation'),
      'abi_survey_id' => $this->t('Survey ID'),
      'abi_survey_title' => $this->t('Survey title'),
      'abi_question' => $this->t('Question'),
      'abi_response' => $this->t('Response'),
      'abi_earned_points' => $this->t('Earned points'),
      'abi_program_name' => $this->t('Program name'),
    ];
    return $fields;
  }

}
