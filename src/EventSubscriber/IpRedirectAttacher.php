<?php

namespace Drupal\embargoes\EventSubscriber;

use Drupal\embargoes\Access\EmbargoedAccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
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
    $route_name = $response->getRequest()->attributes->get(RouteObjectInterface::ROUTE_NAME);
    $redirect_url = NULL;
    // Redirect for nodes.
    if (substr($route_name, 0, 11) == 'entity.node') {
      $node = $response->getRequest()->attributes->get('node');
      if ($node) {
        $redirect_url = $this->nodeAccess->getIpEmbargoedRedirectUrl($node, $this->user);
      }
    }
    // Redirect for media.
    elseif (substr($route_name, 0, 12) == 'entity.media') {
      $media = $response->getRequest()->attributes->get('media');
      if ($media) {
        $redirect_url = $this->mediaAccess->getIpEmbargoedRedirectUrl($media, $this->user);
      }
    }
    // Redirect for files.
    elseif (substr($route_name, 0, 11) == 'entity.file') {
      $file = $response->getRequest()->attributes->get('file');
      if ($file) {
        $redirect_url = $this->fileAccess->getIpEmbargoedRedirectUrl($file, $this->user);
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
