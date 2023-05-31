<?php

namespace Drupal\ucsf_eds_profiles;

use Drupal\node\NodeInterface;
use Symfony\Component\Ldap\Entry;

/**
 * Provides methods to manager UCSF EDS Profiles from LDAP.
 */
interface UcsfEdsProfilesLdapManagerInterface {

  /**
   * Provides map of Drupal field name to EDS property.
   */
  public const EDS_FIELD_MAP = [
    'field_ucsfeds_address' => 'postalAddress',
    'field_ucsfeds_addressp' => 'postalAddress',
    'field_ucsfeds_degrees' => 'ucsfEduDegree',
    'field_ucsfeds_displayname' => 'displayName',
    'field_ucsfeds_email' => 'mail',
    'field_ucsfeds_emailreleasecode' => 'ucsfEduMailReleaseCode',
    'field_ucsfeds_entryreleasecode' => 'ucsfEduEntryReleaseCode',
    'field_ucsfeds_fax' => 'facsimileTelephoneNumber',
    'field_ucsfeds_faxreleasecode' => 'ucsfEduFacsimileTelephoneNumberReleaseCode',
    'field_ucsfeds_firstname' => 'givenName',
    'field_ucsfeds_lastname' => 'sn',
    'field_ucsfeds_middlename' => 'initials',
    'field_ucsfeds_payrolltitle' => 'title',
    'field_ucsfeds_phone' => 'telephoneNumber',
    'field_ucsfeds_phonep' => 'telephoneNumber',
    'field_ucsfeds_phonereleasecode' => 'ucsfEduTelephoneNumberReleaseCode',
    'field_ucsfeds_phonereleasecodep' => 'ucsfEduTelephoneNumberReleaseCode',
    'field_ucsfeds_preferredfirstname' => 'ucsfEduPreferredGivenName',
    'field_ucsfeds_preferredpronoun' => 'ucsfEduPreferredPronoun',
    'field_ucsfeds_primarydeptorunit' => 'ucsfEduDepartmentName',
    'field_ucsfeds_ucid' => 'ucsfEduIDNumber',
    'field_ucsfeds_uid' => 'uid',
    'field_ucsfeds_workingtitle' => 'ucsfEduWorkingTitle',
  ];

  /**
   * Provides map of Drupal field name to profiles property.
   */
  public const PROFILES_FIELD_MAP = [
    'field_ucsfeds_prfawardshonors' => 'AwardOrHonors',
    'field_ucsfeds_prfcollabinterests' => 'CollaborationInterests',
    'field_ucsfeds_prffreetextkw' => 'FreetextKeywords',
    'field_ucsfeds_prfkeywords' => 'Keywords',
    'field_ucsfeds_prfnarrative' => 'Narrative',
    'field_ucsfeds_prfpublications' => 'Publications',
    'field_ucsfeds_prfurl' => 'ProfilesURL',
  ];

  /**
   * Provides Drupal field name map to publications property.
   */
  public const PUBLICATIONS_FIELD_MAP = [
    'title' => 'Title',
    'field_ucsfpfpub_authorlist' => 'AuthorList',
    'field_ucsfpfpub_date' => 'Date',
    'field_ucsfpfpub_pmid' => 'PMID',
    'field_ucsfpfpub_publication' => 'Publication',
    'field_ucsfpfpub_publicationid' => 'PublicationID',
    'field_ucsfpfpub_sourcename' => 'PublicationSourceName',
    'field_ucsfpfpub_sourceurl' => 'PublicationSourceURL',
  ];

  /**
   * Synchronizes the provided node with UCSF EDS Profiles using LDAP.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The UCSF Profiles node to update.
   *
   * @return bool
   *   TRUE if the node was updated.
   */
  public function sync(NodeInterface $node): bool;

  /**
   * Performs an LDAP search.
   *
   * @param string $basedn
   *   The base DN string. This is usually provided by the other methods
   *   automagically.
   * @param string $filter
   *   The filter to query against.
   *
   * @return \Symfony\Component\Ldap\Entry|null|false
   *   The LDAP entry or NULL if something bad happened.
   *
   * @internal
   */
  public function search(string $basedn, string $filter): Entry|FALSE|null;

  /**
   * Searches LDAP for the provided email.
   *
   * @param string $email
   *   The email address.
   *
   * @return \Symfony\Component\Ldap\Entry|null|false
   *   The LDAP entry or NULL if something bad happened.
   */
  public function searchByEmail(string $email): Entry|FALSE|null;

  /**
   * Searches LDAP for the provided UCSF ID.
   *
   * @param string $ucid
   *   The UCSF Profile ID.
   *
   * @return \Symfony\Component\Ldap\Entry|null|false
   *   The LDAP entry or NULL if something bad happened.
   */
  public function searchByUcid(string $ucid): Entry|FALSE|null;

  /**
   * Searches LDAP for the provided Department Number.
   *
   * @param string $dept_num
   *   The Department number. Undocumented.
   *
   * @return \Symfony\Component\Ldap\Entry|null|false
   *   The LDAP entry or NULL if something bad happened.
   */
  public function searchByDept(string $dept_num): Entry|FALSE|null;

  /**
   * Searches LDAP by address type.
   *
   * @param string $uid
   *   Undocumented.
   * @param string $address_type
   *   Defaults to "Campus address". Undocumented.
   *
   * @return \Symfony\Component\Ldap\Entry|null|false
   *   The LDAP entry or NULL if something bad happened.
   */
  public function searchAddressByUid(string $uid, string $address_type): Entry|FALSE|null;

  /**
   * Whether LDAP is bound or not.
   *
   * @return bool
   *   TRUE if LDAP is usable.
   */
  public function hasLdap(): bool;

}
