<?php

namespace Drupal\smart_content_preview\EventSubscriber;

use Drupal\smart_content\Event\AttachDecisionSettingsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class PreviewSettingsEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      AttachDecisionSettingsEvent::EVENT_NAME => 'alterSettings',
    ];
  }

  /**
   * Alter decision settings to be passed to JS.
   *
   * @param \rupal\smart_content\Event\AttachDecisionSettingsEvent $event
   *   Config crud event.
   */
  public function alterSettings(AttachDecisionSettingsEvent $event) {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    if (!in_array('administrator', $roles) && !in_array('editor', $roles)) {
      return;
    }
    // Attached settings from Decision.
    $settings = &$event->getSettings();

    // Load segment preview config.
    $config = \Drupal::configFactory()->get('smart_content_preview.config');

    if (!empty($settings['segments'])) {
      $segment_preview_values = [];
      $preview_mode = FALSE;

      // Determine if segment set is in preview mode.
      foreach ($settings['segments'] as $uuid => &$segment) {
        // Get preview config value for segment.
        $segment_preview_values[$uuid] = $config->get('segment_set_preview.' . $uuid) ?? 0;

        // If any segment has preview set, set preview mode.
        $preview_mode |= $segment_preview_values[$uuid];
      }

      // If in preview mode, alter Decision conditions.
      if ($preview_mode) {
        foreach ($settings['segments'] as $uuid => &$segment) {
          // Negate sibling segments that aren't in preview mode.
          $negate = empty($segment_preview_values[$uuid]) ?? TRUE;

          // Replace conditions.
          $segment['conditions'] = [
            'is_true' => [
              'field' => [
                'pluginId' => 'is_true',
                'type' => 'plugin:is_true',
                'negate' => $negate,
                'unique' => '',
              ],
              'settings' => [
                'negate' => $negate,
              ],
            ]
          ];
        }
      }
    }
  }

}
