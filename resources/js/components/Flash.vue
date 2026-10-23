<template>
    <transition name="slide-fade">
        <div class="alert alert-flash" :class="'alert-'+level" v-show="show" v-text="message"></div>
    </transition>
</template>

<script>
    // Styles are in resources/sass/app.scss.
    export default {
        props: ['message', 'level'],

        data() {
            return {
                show: false,
            }
        },

        created() {
            if (this.message) {
                this.flash();
            }
        },

        methods: {
            flash() {
                setTimeout(() => {
                    this.show = true;
                }, 50);

                this.hide();
            },

            hide() {
                setTimeout(() => {
                    this.show = false;
                }, 5000);
            }
        }
    };
</script>
