import buildRoutes from 'ember-engines/routes';

export default buildRoutes(function () {
    // 8.2 Forecasting — FR-3..5, FR-19..22
    this.route('forecast', { path: '/' }, function () {
        this.route('index', { path: '/' });
    });

    // 8.3 Scheduling — FR-6..8, FR-23, FR-50/51, FR-100/101
    this.route('scheduler', function () {
        this.route('index', { path: '/' });
    });

    // 8.8 Quality Control — FR-27..30, FR-58
    this.route('qc', function () {
        this.route('index', { path: '/' });
        this.route('review', { path: '/:public_id' });
    });

    // 8.13 Campaigns — FR-43..45
    this.route('campaigns', function () {
        this.route('index', { path: '/' });
        this.route('details', { path: '/:public_id' });
    });

    // 8.10 Warranty & Claims — FR-36/37
    this.route('claims', function () {
        this.route('index', { path: '/' });
    });

    // 8.12 RMA / Depot Returns — FR-41/42
    this.route('rma', function () {
        this.route('index', { path: '/' });
    });

    // 8.15 Service Intake — FR-56
    this.route('intake', function () {
        this.route('index', { path: '/' });
    });
});
