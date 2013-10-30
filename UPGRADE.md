# Upgrade #

In this early stage there are no upgrade tools available for upgrading from an older version of LiteCart. A manual upgrade is a drop-on-top set of files and SQL modifications.

## LiteCart 1.0.1-dev to 1.0.1 ##
	UPDATE `lc_settings` SET value = 0 WHERE value = 'false';
	UPDATE `lc_settings` SET value = 1 WHERE value = 'true';

## LiteCart 1.0.1. to 1.0.1.1 ##

  No changes

## LiteCart 1.0.1. to 1.0.1.2 ##

  No changes

## LiteCart 1.0.1.2 to 1.0.1.3 ##

  No changes

## LiteCart 1.0.1.3 to 1.0.1.4 ##

  No changes

