import getRedisClient from './redis-client';
import { FeatureFlags } from '../types';

export const defaultFeatureFlags: FeatureFlags = {
    report_browers_errors_to_sentry: false,
    login_saml: false,
    oauth2_switch_user: false,
    votes_min: false,
    blog: false,
    calendar: false,
    login_facebook: false,
    privacy_policy: false,
    members_list: false,
    captcha: false,
    beta__admin_editor: false,
    beta__questionnaire_result: false,
    consent_external_communication: false,
    consent_internal_communication: false,
    newsletter: false,
    profiles: false,
    projects_form: false,
    project_trash: false,
    search: false,
    share_buttons: false,
    shield_mode: false,
    registration: false,
    phone_confirmation: false,
    reporting: false,
    themes: false,
    districts: false,
    user_type: false,
    votes_evolution: false,
    restrict_registration_via_email_domain: false,
    export: false,
    server_side_rendering: false,
    zipcode_at_register: false,
    consultation_plan: false,
    display_map: false,
    sso_by_pass_auth: false,
    allow_users_to_propose_events: false,
    secure_password: false,
    restrict_connection: false,
    login_franceconnect: false,
    read_more: false,
    display_pictures_in_depository_proposals_list: false,
    external_project: false,
    multilangue: false,
    display_pictures_in_event_list: false,
    beta__emailing: false,
    beta__emailing_parameters: false,
    beta__emailing_group: false,
    proposal_revisions: false,
    unstable__new_consultation_page: false,
    import_proposals: false,
    beta__analytics_page: false,
    http_redirects: false,
    unstable__project_admin: false,
    developer_documentation: false,
    export_legacy_users: false,
    graphql_query_analytics: false,
    noindex_on_profiles: false,
    new_project_card: false,
    indexation: false,
    twilio: false,
    remind_user_account_confirmation: false,
    unstable__anonymous_questionnaire: false,
    login_cas: false,
    login_openid: false,
    public_api: false,
    sentry_log: false,
    versions: false,
    unstable__paper_vote: false,
    graphql_introspection: false,
    helpscout_beacon: false,
    api_sendinblue: false,
    proposal_sms_vote: false,
    unstable__organizations: false,
};

const getRedisFeatureFlagKey = (flag: string) => {
    return `${process.env.SYMFONY_REDIS_PREFIX}feature_toggle__TOGGLE__${flag}`;
};

export const decodePHPFlag = (encodedFlag: string): boolean => {
    return encodedFlag.includes(';i:2;');
};

const getFeatureFlags = async (): FeatureFlags => {
    const redisClient = await getRedisClient();

    const featureFlags = defaultFeatureFlags;

    for (const flag of Object.keys(featureFlags)) {
        const flagKey = getRedisFeatureFlagKey(flag);
        const encodedPHPFlag: string | null = await redisClient.get(flagKey);
        featureFlags[flag] = encodedPHPFlag ? decodePHPFlag(encodedPHPFlag) : !!encodedPHPFlag;
    }

    return featureFlags;
};

export default getFeatureFlags;
