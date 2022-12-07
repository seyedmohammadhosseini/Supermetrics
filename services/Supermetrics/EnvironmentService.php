<?php

namespace Supermetrics;

/**
 * Environment Service
 * 
 * The environment file is `environment.cfg` located at the root.
 * The file itself is not committed to the repo, but created where the code is deployed.
 * There is a sample file as an example called `environment.sample.cfg`
 * 
 */
class EnvironmentService {

    /**
     * getEnvironmentSetting
     *
     * If the `environment.cfg` file is not found, or if the setting isn't there,
     * This will return an empty string.
     *
     * @param  String $name - the setting name
     *
     * @return String - the setting value
     */
    public function getEnvironmentSetting($name, $sectionName = null) {

        $configFile    = __DIR__ . "/../../environment.cfg";
        $configuration = file_exists($configFile) ? parse_ini_file($configFile, true) : [];

        if (!empty($sectionName)) {
            return isset($configuration[$sectionName][$name]) ? $configuration[$sectionName][$name] : "";
        } else {
            return isset($configuration[$name]) ? $configuration[$name] : "";
        }
    }
}
