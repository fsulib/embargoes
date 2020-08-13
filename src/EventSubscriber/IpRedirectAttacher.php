<?php

namespace Drupal\embargoes\EventSubscriber;

use Drupal\embargoes\Access\EmbargoedAccessInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\media\MediaInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Attaches an IP redirect to requests that require one.
 */
class IpRedirectAttacher implements EventSubscriberInterface {

  /**
   * Embargoed node access interface.
   *
   * @var \Drupal\embargoes\Access\EmbargoedAccessInterface
   */
  protected $nodeAccess;

  /**
   * Embargoed media access interface.
   *
   * @var \Drupal\embargoes\Access\EmbargoedAccessInterface
   */
  protected $mediaAccess;

  /**
   * Embargoed file access interface.
   *
   * @var \Drupal\embargoes\Access\EmbargoedAccessInterface
   */
  protected $fileAccess;

  /**
   * The currently logged in user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructor.
   */
  public function __construct(EmbargoedAccessInterface $node_access, EmbargoedAccessInterface $media_access, EmbargoedAccessInterface $file_access, AccountInterface $user) {
    $this->nodeAccess = $node_access;
    $this->mediaAccess = $media_access;
    $this->fileAccess = $file_access;
    $this->user = $user;
  }

  /**
   * Attaches an IP redirect to requests that require one.
   *
   * @param Symfony\Component\HttpKernel\Event\GetResponseEvent $response
   *   The initial response.
   */
  public function attachIpRedirect(GetResponseEvent $response) {
    $redirect_url = NULL;
    // Cycle through all attributes; the first one we get back that's restricted
    // means redirection is necessary.
    foreach ($response->getRequest()->attributes->all() as $attribute) {
      if ($attribute instanceof NodeInterface) {
        $redirect_url = $this->nodeAccess->getIpEmbargoedRedirectUrl($attribute, $this->user);
        break;
      }
      if ($attribute instanceof MediaInterface) {
        $redirect_url = $this->mediaAccess->getIpEmbargoedRedirectUrl($attribute, $this->user);
        break;
      }
      if ($attribute instanceof FileInterface) {
        $redirect_url = $this->fileAccess->getIpEmbargoedRedirectUrl($attribute, $this->user);
        break;
      }
    }
    if ($redirect_url) {
      $response->setResponse(new RedirectResponse($redirect_url));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => [
        ['attachIpRedirect'],
      ],
    ];
  }

}
