<x-profile :sharedData="$sharedData" docTitle="Seguidores {{$sharedData['username']}} ">
  @include('profile-followers-only')
</x-profile>