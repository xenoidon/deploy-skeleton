# Introduction

An skeleton for use [Deployer](http://deployer.org) to deployment PHP project.   
See [http://deployer.org](http://deployer.org) for more information and documentation about Deployer.

# Requirements

* PHP 5.6.0 and up.

# Installation

Clone with `git` and run `composer install`

```shell
$ git clone git@https://github.com/xenoidon/deploy-skeleton.git <target-directory>
$ cd <target-directory>
$ composer install
```

or using compose:

```shell
$ composer create-project xenoidon/deploy-skeleton <target-directory>
```

# Usage

Customize `stage/dev.php` or make a copy and write your own stages.

First:  
```shell
$ php vendor/bin/dep deploy:config <stage>
```

Deployments:
```shell
$ php vendor/bin/dep deploy <stage>
```

Using options `-vvv` for debug
```shell
$ php vendor/bin/dep deploy <stage> -vvv
```

