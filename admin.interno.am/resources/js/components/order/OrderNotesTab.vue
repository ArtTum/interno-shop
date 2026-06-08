<script setup>

import CustomTableSecond from "@components/global/CustomTableSecond.vue";
import CustomTextarea from "@components/global/CustomTextarea.vue";
import CustomButton from "@components/global/CustomButton.vue";

import {useStore} from "vuex";
import {computed, ref} from "vue";
import {validate} from "@validation/customValidation.js";

const store = useStore()
const props = defineProps({
    order_notes: {
        type: Array
    },
    order_id: {
        type: Number
    }
});

const newNote = ref(null);

const emits = defineEmits([
    'fetch',
]);

const saveNewNote = async () => {
    const errors = validate(newNote.value);
    if (Object.keys(errors).length > 0) {
        newNote.value.errors = errors;
        return false;
    }

    try {
        await store.dispatch('order/addNote', newNote.value);
        newNote.value = null;
        emits('fetch');
        store.commit('notification/SET_NOTIFICATION', {
            visible: true,
            title: 'Success',
            message: 'Successfully added'
        });
    } catch (reqErrors) {

    }
};

const auth = computed(() => store.getters['auth/getUser']);
</script>

<template>
    <CustomTableSecond
        @addItem="newNote = {
            order_id: order_id,
            note: null,
            noteRules: ['required'],
            errors: {}
        }"
        :button-info="auth.user_group.permissions_by_name.orders[0].can_add ? {
            title: 'Add not',
            emitName: 'add-item',
            icon: 'plus',
            classes: 'bg-meta-3',
            disabled: !!newNote
        } : null"
        title="Order notes"
    >
        <template #header>

        </template>

        <template #content>
            <template v-if="newNote">
                <div class="grid grid-cols-9 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5">
                    <div class="col-span-8 hidden sm:flex flex-col mr-5">
                        <CustomTextarea
                            label="Note *"
                            name="note"
                            :rows="6"
                            v-model="newNote.note"
                            placeholder="Type note..."
                            @keyup="newNote.errors = validate(newNote)"
                            :error="newNote.errors['note']"
                        />
                    </div>
                    <div class="col-span-1 sm:flex flex-col mr-5 mt-6.5">
                        <CustomButton
                            @click="saveNewNote"
                            class="max-w-[100px] flex items-center gap-2 mt-2 rounded bg-primary py-2 px-4.5 font-medium text-white hover:bg-opacity-80"
                            type="button"
                        >
                            <font-awesome-icon :icon="['far', 'floppy-disk']"/>
                            Save
                        </CustomButton>
                    </div>
                </div>
            </template>
            <!-- Table Header -->
            <div
                class="grid grid-cols-9 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 max-md:hidden"
            >
                <div class="col-span-2 flex items-center">
                    <p class="font-medium">Log by</p>
                </div>
                <div class="col-span-7 flex items-center">
                    <p class="font-medium">Note</p>
                </div>
            </div>

            <!-- Table Rows -->
            <template
                v-for="(note, index) in order_notes"
                :key="index"
            >
                <div
                    class="grid grid-cols-9 border-t border-stroke py-4.5 px-4 md:px-6 2xl:px-7.5 max-md:grid-cols-1"
                >
                    <div class="col-span-2 sm:flex flex-col max-md:mb-[6px]">
                        <div class="flex">
                            <p class="font-medium mr-[8px] md:hidden">Log by: </p>
                            <div >
                                <template v-if="note.user_id">
                                    <a
                                        :href="'/users/list/update/' + note.user_id"
                                        target="_blank"
                                    >
                            <span class="text-sm font-medium text-black hover:text-primary">{{
                                    note.log_by
                                }}</span>
                                        <font-awesome-icon
                                            class="text-black-2 ml-2 hover:text-primary text-sm"
                                            :icon="['fass', 'up-right-from-square']"
                                        />
                                    </a>
                                </template>
                                <template v-else>
                                    <p class="text-sm font-medium text-black">{{ note.log_by }}</p>
                                </template>
                                <p class="text-sm font-medium">{{ note.created_at }}</p>
                            </div>

                        </div>

                    </div>
                    <div class="col-span-7 sm:flex flex-col">
                        <div class="flex">
                            <p class="font-medium mr-[8px] md:hidden">Note: </p>
                            <p class="text-sm font-medium text-black max-md:mt-[3px]" v-html="note.note"></p>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </CustomTableSecond>
</template>

<style lang="scss">
    .table-default-holder {
        min-width: unset;
        > div:first-child {
            flex-wrap: wrap;
            gap:7px;
            @media (max-width: 576px) {
              gap: 9px;
            }
          > div {
              min-width: unset;
              @media (max-width: 576px) {
                width: auto;

              }
              button {
                  @media (max-width: 576px) {
                      margin-left: 0;
                  }
              }
              label {
                  @media (max-width: 576px) {
                    text-align: left;
                  }
              }
              .multiselect {
                  width: 200px;
              }
          }
        }
    }
</style>
