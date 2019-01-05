<template>
    <div style="display: inline-block;">
        <a v-if="linkOnly === ''" v-bind:href="route" v-on:click.prevent="submit()"><slot>Delete</slot></a>
        <button v-else type="submit" class="btn btn-xs btn-danger" v-on:click="submit()"><slot>Delete</slot></button>
        <form class="hidden" method="POST" v-bind:action="route">
            <input type="hidden" name="_token" v-bind:value="csrfToken">
            <input type="hidden" name="_method" value="DELETE">
        </form>
    </div>
</template>

<script>
    export default {
        props: ['route', 'link-only', 'message'],
        methods: {
            submit: function () {
                let confirmationMessage = this.message ? this.message : 'Are you sure you want to delete this object?';
                if (!confirm(confirmationMessage)) return;
                this.$el.querySelector('form').submit();
            }
        },
        computed: {
            csrfToken: function () {
                return $('meta[name=csrf-token]').attr('content');
            }
        }
    }
</script>
