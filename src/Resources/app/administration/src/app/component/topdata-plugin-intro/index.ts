import template from './template.twig';
import './style.scss';

const { Component } = Shopware;

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
                return `https://topdata.de/dokumentation/${this.pluginName}`;
            }
            return 'https://topdata.de';
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
