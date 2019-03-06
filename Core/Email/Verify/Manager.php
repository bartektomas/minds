<?php
/**
 * Verify email manager
 */
namespace Minds\Core\Email\Verify;

use Minds\Core\Security\SpamBlocks;

class Manager
{

    /** @var $service */
    private $service;

    /** @var $spamBlocksManager */
    private $spamBlocksManager;

    /** @var $bannedDomains */
    private $bannedDomains = [
        'annomails.com',
        'emailweb.xyz',
        'buydiscountdeal.com',
        'palantirmails.com',
    ];

    public function __construct($service = null, $spamBlocksManager = null)
    {
        $this->service = $service ?: new Services\Kickbox;
        $this->spamBlocksManager = $spamBlocksManager ?: new SpamBlocks\Manager;
    }

    /**
     * Verify if an email is valid
     * @param string $email
     * @return bool
     */
    public function verify($email)
    {
        $domain = explode('@', strtolower($email))[1];
        if (in_array($domain, $this->bannedDomains)) {
            return false;
        }

        $hash = hash('sha256', $email);
        $spamBlock = new SpamBlocks\SpamBlock;
        $spamBlock->setKey('email_hash')
            ->setValue($hash);
        
        if ($this->spamBlocksManager->isSpam($spamBlock)) {
            return false;
        }

        if (!$this->service->verify($email)) {
            $this->spamBlocksManager->add($spamBlock);
            return false;
        }

        return true;
    }

}
