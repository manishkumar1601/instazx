<?php
declare(strict_types=1);

namespace Ayesh\InstagramDownload;

class InstagramDownload {

	private string $input_url;

	private string $id;

	private string $type = 'image';

	private string $download_url;

	/**
	 * @var array<string>
	 */
	private array $meta_values = [];

  private const INSTAGRAM_DOMAIN = 'instagram.com';

  /**
   * @param string $url
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(string $url) {
    $this->setUrl($url);
  }

  /**
   * @param string $url
   *
   * @throws \InvalidArgumentException
   */
  private function setUrl(string $url): void {
    $id = $this->validateUrl($url);
    $this->id = $id;
    $this->input_url = $url;
  }

  /**
   * Returns the type of data: `image` or `video`.
   *
   * @return string
   * @throws \RuntimeException
   */
  public function getType(): string {
    if (!isset($this->download_url)) {
      $this->process();
    }
    return $this->type;
  }

  /**
   * @param bool $force_download
   *
   * @return string
   * @throws \RuntimeException
   */
  public function getDownloadUrl(bool $force_download = true): string {
    if (!isset($this->download_url)) {
      $this->process();
    }

    if ($force_download) {
      if (\strpos($this->download_url, '?') !== false) {
        return $this->download_url . '&dl=1';
      }

      return $this->download_url . '?dl=1';
    }
    return $this->download_url;
  }

  /**
   *
   * @throws \RuntimeException
   */
  private function process(): void {
    $values = $this->fetch($this->input_url);
    if (empty($values)) {
      throw new \RuntimeException('Error fetching information. Perhaps the post is private.', 3);
    }

    if (!empty($values['og:video'])) {
      $this->type = 'video';
      $this->download_url = $values['og:video'];
      return;
    }

    if (!empty($values['og:image'])) {
      $this->type = 'image';
      $this->download_url = $values['og:image'];
      return;
    }

	  throw new \RuntimeException('Error fetching information. Perhaps the post is private.', 4);
  }

  /**
   * @param string $raw_url
   *
   * @return string
   * @throws \InvalidArgumentException
   */
  private function validateUrl(string $raw_url): string {
    $url = \parse_url($raw_url);
    if ($url === false || empty($url['host'])) {
      throw new \InvalidArgumentException('Invalid URL');
    }

    $url['host'] = \strtolower($url['host']);

    if ($url['host'] !== self::INSTAGRAM_DOMAIN && $url['host'] !== 'www.' . self::INSTAGRAM_DOMAIN) {
      throw new \InvalidArgumentException('Entered URL is not an ' . self::INSTAGRAM_DOMAIN . ' URL.');
    }

    if (empty($url['path'])) {
      throw new \InvalidArgumentException('No image or video found in this URL');
    }

    $args = \explode('/', $url['path']);
    if (!empty($args[1]) && ($args[1] === 'p' || $args[1] === 'tv') && isset($args[2][4]) && !isset($args[2][255])) {
      return $args[2];
    }

    if (!empty($args[2]) && ($args[2] === 'p' || $args[2] === 'tv') && !isset($args[3][255]) && isset($args[3][4], $args[1][4])) {
	  return $args[3];
	}

    throw new \InvalidArgumentException('No image or video found in this URL');
  }

	/**
	 * @param string $url
	 *
	 * @return array<string>
	 */
  private function fetch(string $url): array {
    $curl = \curl_init($url);

    if (!$curl) {
      throw new \RuntimeException('Unable to initialize curl.', 12);
    }

    \curl_setopt($curl, \CURLOPT_FAILONERROR, true);
    \curl_setopt($curl, \CURLOPT_FOLLOWLOCATION, true);
    \curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
    \curl_setopt($curl, \CURLOPT_TIMEOUT, 15);

    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
      \curl_setopt($curl, \CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    }

    $response = \curl_exec($curl);

    \curl_close($curl);

    if(!empty($response) && \is_string($response)) {
      return $this->parse($response);
    }

    throw new \RuntimeException('Could not fetch data.');
  }

	/**
	 * @param string $html
	 *
	 * @return array<string>
	 */
  private function parse(string $html): array {
    $raw_tags = [];
    $this->meta_values = [];

    \preg_match_all('/<meta[^>]+="([^"]*)"[^>]' . '+content="([^"]*)"[^>]+>/i', $html, $raw_tags);

    if(!empty($raw_tags)) {
      $multi_value_tags = \array_unique(\array_diff_assoc($raw_tags[1], \array_unique($raw_tags[1])));
      foreach ($raw_tags[1] as $i => $tag) {
        $has_multiple_values = false;

        foreach($multi_value_tags as $multi_tag) {
          if($tag === $multi_tag) {
            $has_multiple_values = true;
          }
        }

        if($has_multiple_values) {
          $this->meta_values[$tag][] = $raw_tags[2][$i];
        }
        else {
          $this->meta_values[$tag] = $raw_tags[2][$i];
        }
      }
    }

    if (empty($this->meta_values)) {
      return [];
    }

    return $this->meta_values;
  }
}
