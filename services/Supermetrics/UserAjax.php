<?php

// A very simple user class for ajax purposes

namespace Supermetrics;

class UserAjax {

    /**
     * Summary of getUsername
     * @param mixed $params [
     *  - email string
     * ]
     * 
     * @return string
     */
    public function getUsername($params) {

        $enteredEmailAddress       = !empty($params['email']) ? strtolower(trim($params['email'])) : "";
        $enteredMasterEmailAddress = !empty($params['master_email']) ? strtolower(trim($params['master_email'])) : "";

        if (
            empty($enteredEmailAddress)
            && empty($enteredMasterEmailAddress)
        ) {
            throw new \Exception('Enter email address.');
        }

        if (
            (
                !empty($enteredEmailAddress)
                && !filter_var($enteredEmailAddress, FILTER_VALIDATE_EMAIL)
            ) || (
                !empty($enteredMasterEmailAddress)
                && !filter_var($enteredMasterEmailAddress, FILTER_VALIDATE_EMAIL)
            )
        ) {
            throw new \Exception('Email address is not valid.');
        }

        $mainMasterEmail = !empty($enteredEmailAddress) ? $enteredEmailAddress : $enteredMasterEmailAddress;

        // The master email address is {$mainMasterEmail}.

        $databaseService = \Supermetrics::get('DatabaseService');

        $userInformation = $databaseService->getRow(
            "SELECT username FROM users WHERE email = ? ",
            [
                $mainMasterEmail
            ]
        );

        if (empty($userInformation['email'])) {
            throw new \Exception('No account is associated with the email provided..');
        }

        return $userInformation['username'];
    }
}
