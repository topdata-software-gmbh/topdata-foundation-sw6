import TopdataAdminApiClient from '../service/TopdataAdminApiClient';

/**
 * Fix for "TS2304: Cannot find name Shopware"
 * TODO: check https://developer.shopware.com/docs/guides/plugins/plugins/administration/the-shopware-object.html
 */
declare var Shopware: any;

// Register the API client service
Shopware.Service().register('TopdataAdminApiClient', (container) => {
    const initContainer = Shopware.Application.getContainer('init');
    return new TopdataAdminApiClient(initContainer.httpClient, container.loginService);
});
