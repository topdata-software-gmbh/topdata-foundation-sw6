/**
 * TopdataAdminApiClient.ts - Custom API client service for interacting with Shopware's API
 * 2024-11-06 created
 * This TypeScript class extends the ApiService to provide simplified GET, POST, PUT, and DELETE requests
 * for a Shopware-based API. It includes custom headers and uses a base path for API calls.
 *
 * example use:
 *
 * const client = Shopware.Service().get('TopdataAdminApiClient')
 *
 * or you can inject the service in your vue component:
 * {
 *     inject: ['TopdataAdminApiClient'],
 *     methods: {
 *         onClick() {
 *             this.TopdataAdminApiClient.get('/api/topdata/test').then((response) => {
 *                 console.log(response);
 *             });
 *         }
 *     }
 * }
 */

/**
 * Fix for "TS2304: Cannot find name Shopware"
 * TODO: check https://developer.shopware.com/docs/guides/plugins/plugins/administration/the-shopware-object.html
 */
declare var Shopware: any;

const ApiService = Shopware.Classes.ApiService;

class TopdataAdminApiClient extends ApiService {
    /**
     * Constructs the API client instance.
     * @param httpClient - The HTTP client used for making requests.
     * @param loginService - The login service for handling authentication.
     */
    constructor(httpClient: any, loginService: any) {
        // Initialize the parent ApiService with the provided httpClient and loginService.
        super(httpClient, loginService, '');
    }

    /**
     * Sends a GET request to the specified path.
     * @param path - The API endpoint path.
     * @returns A promise that resolves with the API response.
     */
    get(path: string): Promise<any> {
        const url = this.getApiBasePath() + path;
        return this.httpClient.get(
            url,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response: any) => {
            Shopware.State.dispatch('notification/createNotification', {
                title: 'Success',
                message: 'GET request successful',
                type: 'success',
                // Add these properties for Pinia compatibility
                system: true
            });
            return ApiService.handleResponse(response);
        });
    }

    /**
     * Sends a POST request to the specified path with provided data.
     * @param path - The API endpoint path.
     * @param data - The payload for the POST request.
     * @returns A promise that resolves with the API response.
     */
    post(path: string, data: any): Promise<any> {
        const url = this.getApiBasePath() + path;
        return this.httpClient.post(
            url,
            data,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response: any) => {
            return ApiService.handleResponse(response);
        });
    }

    /**
     * Sends a PUT request to the specified path with provided data.
     * @param path - The API endpoint path.
     * @param data - The payload for the PUT request.
     * @returns A promise that resolves with the API response.
     */
    put(path: string, data: any): Promise<any> {
        const url = this.getApiBasePath() + path;
        return this.httpClient.put(
            url,
            data,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response: any) => {
            return ApiService.handleResponse(response);
        });
    }

    /**
     * Sends a DELETE request to the specified path.
     * @param path - The API endpoint path.
     * @returns A promise that resolves with the API response.
     */
    delete(path: string): Promise<any> {
        const url = this.getApiBasePath() + path;
        return this.httpClient.delete(
            url,
            {
                headers: this.getBasicHeaders()
            }
        ).then((response: any) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default TopdataAdminApiClient;
