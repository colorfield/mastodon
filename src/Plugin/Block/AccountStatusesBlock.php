<?php

namespace Drupal\mastodon\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mastodon\Mastodon;

/**
 * Provides a 'AccountStatusesBlock' block.
 *
 * @Block(
 *  id = "mastodon_account�_statuses_block",
 *  admin_label = @Translation("Mastodon account statuses"),
 * )
 */
class AccountStatusesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\mastodon\Mastodon definition.
   *
   * @var \Drupal\mastodon\Mastodon
   */
  protected $mastodonApi;

  /**
   * Constructs a new AccountStatusesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        Mastodon $mastodon_api
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mastodonApi = $mastodon_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mastodon.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'mastodon_account' => '',
      'local_only' => 1,
      'limit' => 10,
      'exclude_replies' => 1,
      'only_media' => 0,
      'pinned' => 0,
    ] + parent::defaultConfiguration();

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // @todo use ajax to cover multiple matches
    $form['mastodon_account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mastodon account'),
      '#description' => $this->t('Mastodon account name with the @.'),
      '#default_value' => $this->configuration['mastodon_account'],
      '#required' => TRUE,
      '#maxlength' => 80,
      '#size' => 64,
      '#weight' => '1',
    ];
    // @todo enable once ready
    $form['local_only'] = [
      '#type' => 'radios',
      '#title' => $this->t('Local only'),
      '#description' => $this->t('Limit the search to local instance only. Needs non local implementation, so currently disabled.'),
      '#options' => [1 => $this->t('Yes'), 0 => $this->t('No')],
      '#default_value' => $this->configuration['local_only'],
      '#required' => TRUE,
      '#disabled' => TRUE,
      '#weight' => '2',
    ];
    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#description' => $this->t('Maximum number of statuses to get (hard limit to 40).'),
      '#default_value' => $this->configuration['limit'],
      '#required' => TRUE,
      '#weight' => '3',
    ];
    // @todo set what follows in collapsed fieldset
    $form['exclude_replies'] = [
      '#type' => 'radios',
      '#title' => $this->t('Exclude replies'),
      '#description' => $this->t('Skip statuses that reply to other statuses.'),
      '#options' => [1 => $this->t('Yes'), 0 => $this->t('No')],
      '#default_value' => $this->configuration['exclude_replies'],
      '#required' => TRUE,
      '#weight' => '4',
    ];
    $form['only_media'] = [
      '#type' => 'radios',
      '#title' => $this->t('Only media'),
      '#description' => $this->t('Only return statuses that have media attachments.'),
      '#options' => [1 => $this->t('Yes'), 0 => $this->t('No')],
      '#default_value' => $this->configuration['only_media'],
      '#required' => TRUE,
      '#weight' => '5',
    ];
    $form['pinned'] = [
      '#type' => 'radios',
      '#title' => $this->t('Pinned'),
      '#description' => $this->t('Only return statuses that have been pinned.'),
      '#options' => [1 => $this->t('Yes'), 0 => $this->t('No')],
      '#default_value' => $this->configuration['pinned'],
      '#required' => TRUE,
      '#weight' => '6',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['mastodon_account'] = $form_state->getValue('mastodon_account');
    $this->configuration['local_only'] = $form_state->getValue('local_only');
    $this->configuration['limit'] = $form_state->getValue('limit');
    $this->configuration['exclude_replies'] = $form_state->getValue('exclude_replies');
    $this->configuration['only_media'] = $form_state->getValue('only_media');
    $this->configuration['pinned'] = $form_state->getValue('pinned');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $accountName = $this->configuration['mastodon_account'];
    // @todo name check
    if (!empty($accountName)) {
      $localOnly = ($this->configuration['local_only'] === 1) ? TRUE : FALSE;
      $userId = $this->mastodonApi->accountUserId($accountName, $localOnly);
      if (is_int($userId)) {
        $params = [
          'limit' => $this->configuration['limit'],
         // @todo
         // 'exclude_replies' => $this->configuration['exclude_replies'],
         // 'only_media' => $this->configuration['only_media'],
         // 'pinned' => $this->configuration['pinned'],
        ];
        $statuses = $this->mastodonApi->accountStatuses($userId, $params);
        $items = [];
        foreach ($statuses as $status) {
          $content = Xss::filter($status['content']);
          // @todo review markup and provide template
          $markup = new FormattableMarkup($content, []);
          $items[] = $markup;
        }
        $build['mastodon-account-statuses'] = [
          '#theme' => 'item_list',
          '#items' => $items,
          '#type' => 'ul',
        ];
      }
      elseif (is_array($userId)) {
        // @todo prevent this with form validation
        $error = $this->t('Multiple matches for the account @name', ['@name' => $accountName]);
        $build = [
          '#markup' => $error,
        ];
      }
    }
    else {
      $error = $this->t('"@name" is not a correct Mastodon account name.', ['@name' => $accountName]);
      $build = [
        '#markup' => $error,
      ];
    }
    // @todo remove, for debug purpose only
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
