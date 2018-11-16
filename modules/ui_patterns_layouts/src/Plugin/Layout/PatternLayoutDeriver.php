<?php

namespace Drupal\ui_patterns_layouts\Plugin\Layout;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ui_patterns\UiPatternsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PatternLayoutDeriver.
 */
class PatternLayoutDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Patterns manager.
   *
   * @var \Drupal\ui_patterns\UiPatternsManager
   */
  protected $manager;

  /**
   * PatternLayoutDeriver constructor.
   *
   * @param \Drupal\ui_patterns\UiPatternsManager $manager
   */
  public function __construct(UiPatternsManager $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.ui_patterns')
    );
  }

  /**
   * Gets the definition of all derivatives of a base plugin.
   *
   * @param \Drupal\Core\Layout\LayoutDefinition|array $base_plugin_definition
   *   The definition array of the base plugin.
   *
   * @return \Drupal\Core\Layout\LayoutDefinition[]
   *   An array of full derivative definitions keyed on derivative id.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $pattern_definition */
    foreach ($this->manager->getDefinitions() as $pattern_definition) {
      $definition = clone $base_plugin_definition;

      // @codingStandardsIgnoreStart
      $definition->set('id', $pattern_definition->id());
      $definition->setLabel(new TranslatableMarkup($pattern_definition->getLabel()));

      // Have to set category as plain string to work around optgroup bug in
      // Display Suite's layout selection form.
      // @todo Remove this when Display Suite bug is fixed.
      $definition->setCategory((string)$definition->getCategory());

      $definition->setThemeHook($pattern_definition->getThemeHook());
      $definition->set('pattern', $pattern_definition->id());
      $definition->set('provider', $pattern_definition->getProvider());
      $regions = [];
      /** @var \Drupal\ui_patterns\Definition\PatternDefinitionField $field */
      foreach ($pattern_definition->getFields() as $field) {
        $regions[$field->getName()]['label'] = $field->getLabel();
      }
      $definition->setRegions($regions);

      if ($pattern_definition->getDescription()) {
        $definition->setDescription(new TranslatableMarkup($pattern_definition->getDescription()));
      }

      $this->derivatives[$pattern_definition->id()] = $definition;
      // @codingStandardsIgnoreEnd
    }

    return $this->derivatives;
  }

}
