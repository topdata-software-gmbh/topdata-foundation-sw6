import template from './template.twig';
import './style.scss';

const { Component } = Shopware;

/**
 * Base URL of the Topdata documentation site.
 *
 * The manual URL of a plugin is derived from its class name, because that is how the docs
 * site derives the slug: the last segment of `extra.shopware-plugin-class`
 * (`TopdataTopFeedSW6`) lowercased (`topdatatopfeedsw6`). Keep this in sync with the docs
 * site when the domain moves.
 */
const DOC_BASE_URL = 'https://docs-v2.topinfra.de';

Component.register('topdata-plugin-intro', {
    template,

    props: {
        pluginName: {
            type: String,
            required: false,
            default: '',
        },
        docUrl: {
            type: String,
            required: false,
            default: '',
        },
    },

    computed: {
        isGerman(): boolean {
            const session = Shopware.State.get('session');
            if (!session || !session.currentLocale) {
                return true;
            }
            return session.currentLocale.startsWith('de-');
        },

        resolvedDocUrl(): string {
            if (this.docUrl) {
                return this.docUrl;
            }
            if (this.pluginName) {
                return `${DOC_BASE_URL}/manuals/${this.pluginName.toLowerCase()}/`;
            }
            return `${DOC_BASE_URL}/`;
        },
    },

    mounted() {
        const cardWrapper = this.$el.closest('.sw-card') || this.$el.closest('.mt-card');
        if (cardWrapper) {
            cardWrapper.style.boxShadow = 'none';
            cardWrapper.style.border = 'none';
            cardWrapper.style.background = 'transparent';
            cardWrapper.style.padding = '0';

            const cardContent =
                cardWrapper.querySelector('.sw-card__content') ||
                cardWrapper.querySelector('.mt-card__content');
            if (cardContent) {
                cardContent.style.padding = '0';
            }
        }
    },
});
