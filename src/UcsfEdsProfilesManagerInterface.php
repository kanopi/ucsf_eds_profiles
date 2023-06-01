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
   * Properties of a Profile include:
   *   - ResearchActivitiesAndFunding[]:
   *      - EndDate: Y-m-d
   *      - Role: string
   *      - SponsorAwardID: string
   *      - StartDate: Y-m-d
   *      - Sponsor: string
   *      - Title: string
   *   - FirstName: string
   *   - GlobalHealth:
   *      - Projects[]: unknown
   *   - PublicationCount: integer
   *   - Address:
   *      - Longitude: double
   *      - Address2: string
   *      - Latitude: double
   *      - Telephone: string
   *      - Address1: string
   *      - Address4: string
   *      - Address3: string
   *      - Fax: string
   *   - ORCID: unknown
   *   - Publications[]:
   *      - Publication: string
   *      - Featured: unknown
   *      - Date: Y-m-d
   *      - Title: string
   *      - PublicationMedlineTA: string
   *      - PublicationSource[]:
   *         -  PublicationSourceURL: uri
   *         - PublicationSourceName: string
   *         - PMID: string (numeric)
   *      - Year: integer
   *      - PublicationCategory: unknown
   *      - Claimed: unknown
   *      - PublicationTitle: string
   *      - PublicationID: uri
   *      - AuthorList: string
   *   - Keywords: string[]
   *   - Title: string
   *   - Department: string
   *   - MediaLinks_beta[]:
   *      - link_url: uri
   *      - link_date: unknown
   *      - link_name: string
   *   - AwardOrHonors[]:
   *      - AwardLabel: string
   *      - AwardStartDate: string (numeric)
   *      - AwardEndDate: string (numeric)
   *      - Summary: string
   *      - AwardConferredBy: unknown
   *   - ClinicalTrials[]:
   *      - EndDate: Y-m-d
   *      - URL: uri
   *      - ID: string
   *      - Conditions: string[]
   *      - StartDate: Y-m-d
   *      - Title: string
   *   - Titles: string[]
   *   - LastName: string
   *   - ProfilesURL: uri
   *   - Email: string|null
   *   - Narrative: string
   *   - FacultyMentoring:
   *      - Types[]: unknown
   *      - Narrative: unknown (string?)
   *   - School: string
   *   - PhotoURL: uri
   *   - WebLinks_beta[]:
   *      - URL: uri
   *      - Label: string
   *   - Education_Training[]:
   *      - location: string (City, Province Code)
   *      - degree: unknown
   *      - end_date: string (m/Y)
   *      - start_date: unknown
   *      - department_or_school: string
   *      - organization: string
   *   - GlobalHealth_beta: unknown
   *   - CollaborationInterests: unknown
   *   - Twitter_beta: string[]
   *   - Name: string
   *   - SlideShare_beta[]: unknown
   *   - Videos[]:
   *      - url: uri
   *      - label: string
   *   - FreetextKeywords: string[]
   *
   * @param array $userOptions
   *   Additional options to pass such as query parameters which would be
   *   provided by the "query" key as an array keyed by parameter name.
   *
   * @return array|false
   *   The normalized result. The JSON returned from the API should have a
   *   Profiles property which is an indexed array, and this returns the first
   *   result of that array.
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
