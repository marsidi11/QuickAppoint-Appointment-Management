<template>
    <div class="input-form">

        <h2 class="form-title">Your Information</h2>

        <form @submit.prevent="submitForm" class="form">
            

            <div class="form-group">
                <label for="name" class="label">Name:</label>
                <input type="text" id="name" v-model="name" placeholder="Name" required class="input">
            </div>

            <div class="form-group">
                <label for="surname" class="label">Surname:</label>
                <input type="text" id="surname" v-model="surname" placeholder="Surname" required class="input">
            </div>

            <div class="form-group">
                <label for="phone" class="label">Phone Number:</label>
                <input type="tel" id="phone" v-model="phone" placeholder="+1 ..." required class="input">
            </div>

            <div class="form-group">
                <label for="email" class="label">Email:</label>
                <input type="email" id="email" v-model="email" placeholder="name@example.com" required class="input">
            </div>

        </form>
    </div>
</template>

<script>

export default {
    name: 'CalendarUserData',

    inject: ['formData'],

    data() {
        return {
            name: '',
            surname: '',
            phone: '',
            email: ''
        };
    },

    watch: {
        name: 'emitUserData',
        surname: 'emitUserData',
        phone: 'emitUserData',
        email: 'emitUserData'
    },

    methods: {
        emitUserData() {
            this.$emit('update-user-data', {
                name: this.name,
                surname: this.surname,
                phone: this.phone,
                email: this.email
            });
        },
    },
};
</script>