<template>
    <transition name="slide-fade">
        <div class="alert alert-flash" :class="'alert-'+level" v-show="show" v-text="body"></div>
    </transition>
</template>

<script>
    export default {
        props: ['message', 'level'],

        data() {
            return {
                body: this.message,
                show: false,
            }
        },

        created() {
            if (this.message) {
                this.flash();
            }
        },

        methods: {
            flash(data) {
                if (data) {
                    this.body = data.message;
                    this.level = data.level;
                }

                setTimeout(() => {
                    this.show = true;
                }, 50);

                this.hide();
            },

            hide() {
                setTimeout(() => {
                    this.show = false;
                }, 3000);
            }
        }
    };
</script>

<style>
    .alert-flash {
        position: fixed;
        right: 25px;
        top: 25px;
        margin: 0;
    }
    /* Enter and leave animations can use different */
    /* durations and timing functions.              */
    .slide-fade-enter-active, .slide-fade-leave-active {
        transition: all .5s ease;
    }
    .slide-fade-enter, .slide-fade-leave-to
        /* .slide-fade-leave-active below version 2.1.8 */ {
        transform: translateX(10px);
        opacity: 0;
    }
</style>
