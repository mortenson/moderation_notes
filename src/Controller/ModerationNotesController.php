<?php

namespace Drupal\moderation_notes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\content_moderation\ModerationInformation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Endpoints for the Moderation Notes module.
 */
class ModerationNotesController extends ControllerBase {

  /**
   * The ModerationInformation service.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInfo;

  /**
   * Constructs a ModerationNotesController.
   *
   * @param \Drupal\content_moderation\ModerationInformation $moderation_information
   *   The ModerationInformation service.
   */
  public function __construct(ModerationInformation $moderation_information) {
    $this->moderationInfo = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_moderation.moderation_information')
    );
  }

}
