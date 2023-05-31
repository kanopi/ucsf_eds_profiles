<?php

namespace Drupal\ucsf_eds_profiles;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides a http client to fetch from public JSON API.
 */
class UcsfEdsProfilesManager implements UcsfEdsProfilesManagerInterface {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The module logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Initialization method.
   *
   * @param \GuzzleHttp\Client $client
   *   The http_client service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger.factory service.
   */
  public function __construct(Client $client, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->httpClient = $client;
    $this->logger = $loggerChannelFactory->get('ucsf_eds_profiles');
  }

  /**
   * {@inheritdoc}
   */
  public function search(array $userOptions): array|FALSE {
    $default_options = [
      'query' => [
        'publications' => 'full',
        'source' => 'ucsf_eds_profiles_drupal10_module',
      ],
    ];

    try {
      $options = NestedArray::mergeDeepArray($default_options, $userOptions);
      $response = $this->httpClient->request('GET', UcsfEdsProfilesManagerInterface::BASE_URL, $options);
      $data = $response->getBody()->getContents();
      $profile = json_decode($data, TRUE);
      if (!empty($profile['Profiles'][0])) {
        return $profile['Profiles'][0];
      }

      $this->logger->error('Malformed profiles data.');
    }
    catch (GuzzleException $e) {
      if ($e->getCode() == 404) {
        return [];
      }

      $this->logger->error('Profiles search failed.', ['exception' => $e]);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function searchByUcid(string $ucid): array|FALSE {
    return $this->search(['query' => ['EmployeeID' => $ucid]]);
  }

  /**
   * {@inheritdoc}
   */
  public function searchByProfileName(string $name): array|FALSE {
    return $this->search(['query' => ['URL' => UcsfEdsProfilesManagerInterface::PROFILE_URL . $name]]);
  }

}
