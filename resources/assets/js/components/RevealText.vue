<template>
    <span tabindex v-bind:class="{ 'text-link': !revealed }" v-on:click="reveal">{{ text }}</span>
</template>

<script>
    export default {
        props: ['hidden'],
        data: function () {
            return {
                text: 'Click to Reveal',
                revealed: false
            }
        },
        methods: {
            reveal: function () {
                this.text = this.hidden;
                this.revealed = true;
            }
        }
    }
</script>
