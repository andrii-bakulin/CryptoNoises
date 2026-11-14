<?php

namespace CryptoNoisesTezos\Jobs;

use CryptoNoisesTezos\Core\TezosAddress;
use CryptoNoisesTezos\Models;
use CryptoNoisesTezos\Vendors;
use CryptoCore\Enum\JobResult;
use Exception;

trait SyncProfiles_Tzprofiles
{
    private function syncTzprofiles(array $taskItem): JobResult
    {
        try
        {
            $contractAdid    = $taskItem['source_adid'];
            $contractAddress = TezosAddress::GetAddressByAdid($contractAdid);

            if ($contractAddress == null)
                throw new Exception("Cannot convert adid to address: $contractAdid", JobResult::kFailedAndClose);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
            // Resolve the profile address (from TzKT)

            $contractItem = Models\ProfilesTzprofilesContracts::model()->getItem($contractAdid);

            if ($contractItem)
            {
                $profileAdid    = $contractItem['profile_adid'];
                $profileAddress = $contractItem['profile_address'];
            }
            else
            {
                $storageData = Vendors\Tzkt::GetContractStorageData($contractAddress);

                if (empty($storageData))
                    throw new Exception("Cannot get storage data from tzkt");

                $profileAdid    = TezosAddress::GetOrCreateAdid($storageData['owner']);
                $profileAddress = $storageData['owner'];

                if (empty($profileAdid) || empty($profileAddress))
                    throw new Exception("Cannot find owner address/adid in storage data");

                Models\ProfilesTzprofilesContracts::model()->create($contractAdid, $contractAddress, $profileAdid, $profileAddress);
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $tzprofileItem = Models\ProfilesTzprofiles::model()->getItem($profileAdid);

            if ($tzprofileItem === null)
                $tzprofileItem = Models\ProfilesTzprofiles::model()->create($profileAdid, $profileAddress);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $data = Vendors\Tzprofiles::GetProfileDetails($profileAddress);

            if ($data === null)
                throw new Exception("Cannot get profile data for: $profileAddress ($contractAddress)", JobResult::kFailedAndPostpone);

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            $errors = [];

            $updateData = [];                # reset everything to null: fields the user removed must be cleared
            $updateData['alias']             = null;
            $updateData['website']           = null;
            $updateData['description']       = null;
            $updateData['logo_url']          = null;
            $updateData['dns']               = null;
            $updateData['twitter']           = null;
            $updateData['discord_name']      = null;
            $updateData['github']            = null;
            $updateData['ethereum_address']  = null;

            foreach ($data as $idx => $item)
            {
                if (count($item) != 4)
                {
                    $errors[] = "Item does not have 4 elements";
                    continue;
                }

                [$keplerId, $content, $type, $action] = $item;

                if (($content = json_decode($content, true)) === null)
                {
                    $errors[] = "Cannot decode content";
                    continue;
                }

                if ($this->syncTzprofiles_BasicProfile($content, $updateData))           continue;
                if ($this->syncTzprofiles_TwitterVerification($content, $updateData))    continue;
                if ($this->syncTzprofiles_DiscordVerification($content, $updateData))    continue;
                if ($this->syncTzprofiles_GitHubVerification($content, $updateData))     continue;
                if ($this->syncTzprofiles_EthereumControl($content, $updateData))        continue;
                if ($this->syncTzprofiles_EthereumAddressControl($content, $updateData)) continue;
                if ($this->syncTzprofiles_DnsVerification($content, $updateData))        continue;

                $errors[] = "Unknown credential types [".join(',',$content['type'])."]";
            }

            // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

            // Only the alias is used (wallet names in bot messages): the other verified claims (social accounts,
            // linked wallets, DNS) of third-party accounts are parsed for validation but not stored
            foreach ($updateData as $field => $value)
                if ($field != 'alias')
                    $updateData[$field] = null;

            Models\ProfilesTzprofiles::model()->save($profileAdid, $updateData);

            if ($errors)
                throw new Exception(join("; ", $errors), JobResult::kFailedAndClose);

            return JobResult::Completed();
        }
        catch (Exception $e)
        {
            $this->lastActionNotice = $e->getMessage();
            return JobResult::FailedWithCode($e->getCode());
        }
    }

    private function syncTzprofiles_BasicProfile($content, &$updateData): bool
    {
        if (!in_array('BasicProfile', $content['type']))
            return false;

        $updateData['alias']         = $content['credentialSubject']['alias'];
        $updateData['website']       = $content['credentialSubject']['website'];
        $updateData['description']   = $content['credentialSubject']['description'];

        if (strpos($content['credentialSubject']['logo'], 'data:') !== 0)
            $updateData['logo_url']  = $content['credentialSubject']['logo'];        # Like, data:image/jpeg;base64......

        return true;
    }

    private function syncTzprofiles_DnsVerification($content, &$updateData): bool
    {
        if (!in_array('DnsVerification', $content['type']))
            return false;

        $dns = $content['credentialSubject']['sameAs'];
        $dns = str_replace('dns:', '', $dns);
        $updateData['dns'] = $dns;
        return true;
    }

    private function syncTzprofiles_TwitterVerification($content, &$updateData): bool
    {
        if (!in_array('TwitterVerification', $content['type']))
            return false;

        $twitter = $content['credentialSubject']['sameAs'];
        $twitter = str_replace('https://twitter.com/', '', $twitter);
        $updateData['twitter'] = $twitter;
        return true;
    }

    private function syncTzprofiles_DiscordVerification($content, &$updateData): bool
    {
        if (!in_array('DiscordVerification', $content['type']))
            return false;

        $discordName = $content['credentialSubject']['sameAs'];
        $discordName = str_replace('urn:discord:', '', $discordName);
        $updateData['discord_name'] = $discordName;
        return true;
    }

    private function syncTzprofiles_GitHubVerification($content, &$updateData): bool
    {
        if (!in_array('GitHubVerification', $content['type']))
            return false;

        $github = $content['credentialSubject']['sameAs'];
        $github = str_replace('https://github.com/', '', $github);
        $updateData['github'] = $github;
        return true;
    }

    private function syncTzprofiles_EthereumControl($content, &$updateData): bool
    {
        if (!in_array('EthereumControl', $content['type']))
            return false;

        $updateData['ethereum_address'] = $content['credentialSubject']['wallet'];
        return true;
    }

    private function syncTzprofiles_EthereumAddressControl($content, &$updateData): bool
    {
        if (!in_array('EthereumAddressControl', $content['type']))
            return false;

        $updateData['ethereum_address'] = $content['credentialSubject']['address'];
        return true;
    }
}
