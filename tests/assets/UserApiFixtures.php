<?php

namespace Tests\assets;

class UserApiFixtures
{
    /**
     * Fake identity token.
     *
     * @var  array
     */
    const FAKE_IDENTITY_TOKEN = [
        'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
        'expires_in' => 3600,
        'token_type' => 'Bearer',
    ];

    /**
     * Fake identity subject ID.
     *
     * @var  string
     */
    const FAKE_IDENTITY_SUBJECT_ID = '00000000-0000-0000-0000-000000000001';
    const FAKE_IDENTITY_SUBJECT_ID2 = '00000000-0000-0000-0000-000000000002';

    /**
     * Testing application ID.
     *
     * @var  integer
     */
    const APPLICATION_ID = 99;

    /**
     * Testing agency ID.
     *
     * @var  string
     */
    const AGENCY_ID = '00000000-0000-0000-0000-000000000002';

    /**
     * Testing Hospice account ID.
     *
     * @var  string
     */
    const HOSPICE_ACCOUNT_ID = '00000000-0000-0000-2222-000000000002';

    /**
     * Testing agency name.
     *
     * @var  string
     */
    const AGENCY_NAME = 'ACME Home Health';

    /**
     * Testing patient ID.
     *
     * @var  string
     */
    const PATIENT_ID = '00000000-0000-0000-0000-000000000003';

    /**
     * Testing patient contact ID.
     *
     * @var  string
     */
    const PATIENT_CONTACT_ID = '00000000-0000-0000-0000-000000000004';
}
