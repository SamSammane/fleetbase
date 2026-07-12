import Model, { attr } from '@ember-data/model';

export default class AvailabilityWindowModel extends Model {
    /** @ids */
    @attr('string') uuid;
    @attr('string') public_id;
    @attr('string') company_uuid;
    @attr('string') subject_uuid;
    @attr('string') subject_type;
    @attr('string') place_uuid;

    /** @attributes */
    @attr('string') segment;
    @attr('string') location_code;
    @attr('string') source;
    @attr('string') status;
    @attr('number') confidence;
    @attr('raw') meta;

    /** @dates */
    @attr('date') starts_at;
    @attr('date') ends_at;
    @attr('date') validated_at;
    @attr('date') created_at;
    @attr('date') updated_at;
}
