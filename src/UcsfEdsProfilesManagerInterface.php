<?php

namespace Drupal\ucsf_eds_profiles;

/**
 * Provides an interface for interacting with the UCSF Profiles API.
 */
interface UcsfEdsProfilesManagerInterface {

  /**
   * The BASE URL for the Profiles API.
   */
  public const BASE_URL = 'https://api.profiles.ucsf.edu/json/v2/';

  /**
   * The base profile URL for a profile (non-API).
   */
  public const PROFILE_URL = 'https://profiles.ucsf.edu/';

  /**
   * Searches via the UCSF EDS Profiles Public JSON API.
   *
   * @param array $userOptions
   *   Additional options to pass such as query parameters which would be
   *   provided by the "query" key as an array keyed by parameter name.
   *
   * @return array|false
   *   The normalized result.
   */
  public function search(array $userOptions): array|FALSE;

  /**
   * Searches for the UCSF EDS Profile by UCID.
   *
   * @param string $ucid
   *   The UCID.
   *
   * @return array|false
   *   The normalized result.
   */
  public function searchByUcid(string $ucid): array|FALSE;

  /**
   * Searches for the UCSF EDS Profile by profile name.
   *
   * This uses the recommended search by full URL rather than the query
   * parameter ProfilesURLName.
   *
   * @param string $name
   *   A profile name e.g. "tung.nguyen".
   *
   * @return array|false
   *   The normalized result.
   */
  public function searchByProfileName(string $name): array|FALSE;

}
