import Model, { attr } from '@ember-data/model';

export default class CampaignModel extends Model {
    /** @ids */
    @attr('string') uuid;
    @attr('string') public_id;
    @attr('string') company_uuid;

    /** @attributes */
    @attr('string') code;
    @attr('string') name;
    @attr('string') description;
    @attr('string') type;
    @attr('string') status;
    @attr('string') priority;
    @attr('string') work_order_category;
    @attr('boolean') bundling_enabled;
    @attr('raw') meta;

    /** @dates */
    @attr('date') starts_at;
    @attr('date') ends_at;
    @attr('date') created_at;
    @attr('date') updated_at;
}
