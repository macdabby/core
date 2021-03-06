<?php
/**
 * @author Robin McCorkell <rmccorkell@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_External\Lib\Backend;

use \OCP\IL10N;
use \OCA\Files_External\Lib\Backend\Backend;
use \OCA\Files_External\Lib\DefinitionParameter;
use \OCA\Files_External\Lib\Auth\AuthMechanism;
use \OCA\Files_External\Service\BackendService;
use \OCA\Files_External\Lib\Auth\PublicKey\RSA;

class SFTP_Key extends Backend {

	public function __construct(IL10N $l, RSA $legacyAuth) {
		$this
			->setIdentifier('\OC\Files\Storage\SFTP_Key')
			->setStorageClass('\OC\Files\Storage\SFTP')
			->setText($l->t('SFTP with secret key login [DEPRECATED]'))
			->addParameters([
				(new DefinitionParameter('host', $l->t('Host'))),
				(new DefinitionParameter('root', $l->t('Remote subfolder')))
					->setFlag(DefinitionParameter::FLAG_OPTIONAL),
			])
			->removeAllowedPermission(BackendService::USER_PERSONAL, BackendService::PERMISSION_CREATE)
			->removeAllowedPermission(BackendService::USER_ADMIN, BackendService::PERMISSION_CREATE)
			->addAuthScheme(AuthMechanism::SCHEME_PUBLICKEY)
			->setLegacyAuthMechanism($legacyAuth)
		;
	}

}
