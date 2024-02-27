<?php

namespace Drupal\saque\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SaqueForm extends FormBase {

  public function getFormId() {
    return 'saque_saque';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['valor_saque'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Digite o valor do saque'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sacar'),
    ];

    if($form_state->isRebuilding()) {
        $output_valor = $form_state->getValue('valor_saque');

        $form['valor_limite'] = [
            '#type' => 'markup',
            '#markup' => $this->t('<b>Valor de Saque:</b> R$ @output_valor<br>', ['@output_valor' => $output_valor]),
        ];

        $form['quantidade_cedulas'] = [
            '#type' => 'markup',
            '#markup' => $this->t('<b>Quantidade de cédulas:</b> @quantidade<br>', ['@quantidade' => $form_state->get('quantidade_cedulas')]),
        ];

        $form['distribuicao_cedulas'] = [
            '#type' => 'markup',
            '#markup' => $this->t('<b>Distribuição de cédulas:</b> <br>@distribuicao', ['@distribuicao' => $form_state->get('distribuicao_cedulas')]),
        ];
    }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $formField = $form_state->getValues();
    $valorSaque = trim($formField['valor_saque']);

    if (!preg_match("/^\d+$/", $valorSaque)) {
      $form_state->setErrorByName('valor_saque', $this->t('Digite apenas números inteiros acima de zero, sem ponto ou vírgula.'));
    }
  }

    public function submitForm(array &$form, FormStateInterface $form_state) {
    $valorSaque = $form_state->getValue('valor_saque');
    $notas = [100, 50, 10, 5, 2, 1];
    $quantidade_cedulas = 0;
    $distribuicao_cedulas = [];
    $quantidade_cedulas_total = 0; // inicializando variavel

    foreach ($notas as $nota) {
        $quantidade_cedulas = 0;
        while ($valorSaque >= $nota) {
            $valorSaque -= $nota;
            $quantidade_cedulas++;
        }
        if ($quantidade_cedulas > 0) {
            $distribuicao_cedulas[] = "$quantidade_cedulas cédula" . ($quantidade_cedulas > 1 ? 's' : '') . " de R$ $nota\n";
            $quantidade_cedulas_total += $quantidade_cedulas;
        }
    }

    $form_state->set('quantidade_cedulas', $quantidade_cedulas_total);
    $form_state->set('distribuicao_cedulas', implode($distribuicao_cedulas));

    $this->messenger()->addStatus($this->t('Saque finalizado.'));
    $form_state->setRebuild();

    }

}
