<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUiStore } from '../../stores/ui'
import { useAuthStore } from '../../stores/auth'

const ui = useUiStore()
const auth = useAuthStore()
const router = useRouter()

// Login
const loginEmail = ref('')
const loginPassword = ref('')
const loginError = ref('')

// Register client
const regName = ref('')
const regEmail = ref('')
const regPassword = ref('')
const regPasswordConfirm = ref('')
const regError = ref('')

// Register store
const storeStep = ref(1)
const storeName = ref('')
const storeBio = ref('')
const storeEmail = ref('')
const storePassword = ref('')
const storePasswordConfirm = ref('')
const storeLogoUrl = ref('')
const storeCoverUrl = ref('')
const storeError = ref('')

const loading = ref(false)

const show = computed(() => ui.modal !== null)

watch(() => ui.modal, () => {
  loginError.value = ''
  regError.value = ''
  storeError.value = ''
  storeStep.value = 1
})

async function doLogin() {
  loginError.value = ''
  if (!loginEmail.value || !loginPassword.value) { loginError.value = 'ALL FIELDS REQUIRED'; return }
  loading.value = true
  try {
    await auth.login({ email: loginEmail.value, password: loginPassword.value })
    ui.closeModal()
    ui.showToast('WELCOME BACK.')
    if (auth.isStoreOwner) router.push('/dashboard')
    else if (auth.isAdmin) router.push('/admin')
  } catch (e: any) {
    const msg = e?.response?.data?.message || 'INVALID CREDENTIALS'
    loginError.value = msg.toUpperCase()
  } finally {
    loading.value = false
  }
}

async function doRegister() {
  regError.value = ''
  if (!regName.value || !regEmail.value || !regPassword.value) { regError.value = 'ALL FIELDS REQUIRED'; return }
  if (regPassword.value !== regPasswordConfirm.value) { regError.value = 'PASSWORDS DO NOT MATCH'; return }
  loading.value = true
  try {
    await auth.register({ name: regName.value, email: regEmail.value, password: regPassword.value, password_confirmation: regPasswordConfirm.value })
    ui.closeModal()
    ui.showToast('ACCOUNT CREATED. WELCOME.')
  } catch (e: any) {
    regError.value = (e?.response?.data?.message || 'REGISTRATION FAILED').toUpperCase()
  } finally {
    loading.value = false
  }
}

async function doRegisterStore() {
  storeError.value = ''
  if (!storeName.value || !storeBio.value || !storeEmail.value || !storePassword.value) { storeError.value = 'ALL FIELDS REQUIRED'; return }
  loading.value = true
  try {
    await auth.registerStore({
      name: storeName.value, bio: storeBio.value,
      email: storeEmail.value, password: storePassword.value,
      password_confirmation: storePasswordConfirm.value,
      logo_url: storeLogoUrl.value, hero_image_url: storeCoverUrl.value,
    })
    ui.closeModal()
    ui.showToast('STORE SUBMITTED FOR APPROVAL.')
  } catch (e: any) {
    storeError.value = (e?.response?.data?.message || 'SUBMISSION FAILED').toUpperCase()
  } finally {
    loading.value = false
  }
}

function closeOnOverlay(e: MouseEvent) {
  if ((e.target as HTMLElement).classList.contains('auth-overlay')) ui.closeModal()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="auth-overlay" @click="closeOnOverlay">

        <!-- LOGIN -->
        <div v-if="ui.modal === 'login'" class="auth-box">
          <button class="auth-close" @click="ui.closeModal()">×</button>
          <div class="auth-title">SIGN IN</div>
          <div class="auth-sub">Access your account and orders.</div>
          <div v-if="loginError" class="auth-err">{{ loginError }}</div>
          <div class="form-field">
            <label class="form-label">Email</label>
            <input v-model="loginEmail" type="email" class="form-input" placeholder="email@example.com" @keyup.enter="doLogin" />
          </div>
          <div class="form-field">
            <label class="form-label">Password</label>
            <input v-model="loginPassword" type="password" class="form-input" placeholder="••••••••" @keyup.enter="doLogin" />
          </div>
          <button class="btn-cta" style="width:100%;font-size:10px;padding:13px;margin-top:4px" :disabled="loading" @click="doLogin">
            {{ loading ? '...' : 'SIGN IN →' }}
          </button>
          <div class="auth-switch">Don't have an account? <button @click="ui.openModal('register')">CREATE ACCOUNT</button></div>
          <div class="auth-switch" style="margin-top:8px">Are you a brand? <button @click="ui.openModal('register-store')">REGISTER STORE</button></div>
        </div>

        <!-- REGISTER CLIENT -->
        <div v-else-if="ui.modal === 'register'" class="auth-box">
          <button class="auth-close" @click="ui.closeModal()">×</button>
          <div class="auth-title">CREATE ACCOUNT</div>
          <div class="auth-sub">Join the movement.</div>
          <div v-if="regError" class="auth-err">{{ regError }}</div>
          <div class="form-field">
            <label class="form-label">Full Name</label>
            <input v-model="regName" class="form-input" placeholder="Your name" />
          </div>
          <div class="form-field">
            <label class="form-label">Email</label>
            <input v-model="regEmail" type="email" class="form-input" placeholder="email@example.com" />
          </div>
          <div class="form-field">
            <label class="form-label">Password</label>
            <input v-model="regPassword" type="password" class="form-input" placeholder="••••••••" />
          </div>
          <div class="form-field">
            <label class="form-label">Confirm Password</label>
            <input v-model="regPasswordConfirm" type="password" class="form-input" placeholder="••••••••" />
          </div>
          <button class="btn-cta" style="width:100%;font-size:10px;padding:13px;margin-top:4px" :disabled="loading" @click="doRegister">
            {{ loading ? '...' : 'CREATE ACCOUNT →' }}
          </button>
          <div class="auth-switch">Already have an account? <button @click="ui.openModal('login')">SIGN IN</button></div>
        </div>

        <!-- REGISTER STORE -->
        <div v-else-if="ui.modal === 'register-store'" class="auth-box">
          <button class="auth-close" @click="ui.closeModal()">×</button>
          <div class="auth-title">REGISTER STORE</div>
          <div class="step-indicator" style="margin-bottom:20px">
            <span class="step-item" :class="{ on: storeStep === 1 }">STEP 1</span>
            <span style="color:var(--color-text-soft);margin:0 6px">—</span>
            <span class="step-item" :class="{ on: storeStep === 2 }">STEP 2</span>
          </div>
          <div v-if="storeError" class="auth-err">{{ storeError }}</div>

          <!-- Step 1 -->
          <template v-if="storeStep === 1">
            <div class="form-field">
              <label class="form-label">Store Name</label>
              <input v-model="storeName" class="form-input" placeholder="Your brand name" />
            </div>
            <div class="form-field">
              <label class="form-label">Bio</label>
              <textarea v-model="storeBio" class="form-input" rows="3" placeholder="Tell your story..." style="resize:vertical" />
            </div>
            <div class="form-field">
              <label class="form-label">Email</label>
              <input v-model="storeEmail" type="email" class="form-input" placeholder="brand@example.com" />
            </div>
            <div class="form-field">
              <label class="form-label">Password</label>
              <input v-model="storePassword" type="password" class="form-input" placeholder="••••••••" />
            </div>
            <div class="form-field">
              <label class="form-label">Confirm Password</label>
              <input v-model="storePasswordConfirm" type="password" class="form-input" placeholder="••••••••" />
            </div>
            <button class="btn-cta" style="width:100%;font-size:10px;padding:13px" @click="storeStep = 2">NEXT: BRAND ASSETS →</button>
          </template>

          <!-- Step 2 -->
          <template v-else>
            <div class="form-field">
              <label class="form-label">Logo URL</label>
              <input v-model="storeLogoUrl" class="form-input" placeholder="https://logo.png" />
            </div>
            <div class="form-field">
              <label class="form-label">Cover Image URL</label>
              <input v-model="storeCoverUrl" class="form-input" placeholder="https://cover.jpg" />
            </div>
            <div style="border:1px dashed var(--color-border-2);padding:32px;text-align:center;margin-bottom:18px">
              <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:var(--color-text-soft)">
                DRAG & DROP IMAGE FILES<br>OR CLICK TO UPLOAD<br>
                <span style="font-size:10px;text-transform:none;margin-top:4px;display:block;font-family:'DM Sans',sans-serif">PNG, JPG up to 5MB</span>
              </div>
            </div>
            <div style="display:flex;gap:10px">
              <button class="btn-out" style="flex:1;font-size:10px;padding:13px" @click="storeStep = 1">← BACK</button>
              <button class="btn-cta" style="flex:2;font-size:10px;padding:13px" :disabled="loading" @click="doRegisterStore">
                {{ loading ? '...' : 'SUBMIT FOR APPROVAL →' }}
              </button>
            </div>
          </template>
          <div class="auth-switch" style="margin-top:12px">Already have an account? <button @click="ui.openModal('login')">SIGN IN</button></div>
        </div>

      </div>
    </Transition>
  </Teleport>
</template>
