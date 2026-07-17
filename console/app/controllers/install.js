import Controller from '@ember/controller';
import { inject as service } from '@ember/service';

export default class InstallController extends Controller {
    @service installation;

    runningLocallyDocsUrl = 'https://fleet-app.qgi.dev/docs/';
    cloudDocsUrl = 'https://fleet-app.qgi.dev/docs/';

    get isRefreshing() {
        return this.installation.isRefreshing;
    }
}
