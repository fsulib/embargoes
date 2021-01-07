<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesEmbargoesServiceInterface.
 */
interface EmbargoesEmbargoesServiceInterface {

  public function getAllEmbargoesByNids($nids);
  public function getCurrentEmbargoesByNids($nids);
  public function getIpAllowedCurrentEmbargoesByNids($nids);
  public function getActiveEmbargoesByNids($nids, $ip, $user);
  public function getActiveNodeEmbargoesByNids($nids, $ip, $user);
  public function getIpAllowedEmbargoes($embargoes);
  public function isUserInExemptUsers($user, $embargo_id);
  public function isUserGroupAdministrator($user, $embargo_id);
  public function isIpInExemptRange($ip, $embargo_id);
  public function getNodeMediaReferenceFields();
  public function getMediaParentNids($mid);
  public function getParentNidsOfFileEntity($file);

}
