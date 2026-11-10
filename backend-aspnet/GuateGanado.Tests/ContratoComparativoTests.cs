using System.Net;
using System.Net.Http.Headers;
using System.Net.Http.Json;
using GuateGanado.Api.Dtos;
using Microsoft.AspNetCore.Mvc.Testing;
using Xunit;

namespace GuateGanado.Tests;

/// <summary>
/// Las tres pruebas equivalentes del contrato comparativo (Hito 2).
/// Son las mismas tres que deben existir en Laravel y en NestJS.
/// </summary>
public class ContratoComparativoTests : IClassFixture<WebApplicationFactory<Program>>
{
    private readonly WebApplicationFactory<Program> _factory;

    public ContratoComparativoTests(WebApplicationFactory<Program> factory) => _factory = factory;

    private async Task<HttpClient> ClienteAutenticadoAsync()
    {
        var client = _factory.CreateClient();

        var login = await client.PostAsJsonAsync("/api/v1/auth/login", new
        {
            email = "admin@guateganado.cr",
            password = "Secret123"
        });

        login.EnsureSuccessStatusCode();
        var body = await login.Content.ReadFromJsonAsync<LoginResponseDto>();

        client.DefaultRequestHeaders.Authorization =
            new AuthenticationHeaderValue("Bearer", body!.Token);

        return client;
    }

    /// <summary>Prueba 1: el listado exige token (401) y responde 200 con metadatos de paginacion.</summary>
    [Fact]
    public async Task Listado_SinToken_Devuelve401_ConToken_Devuelve200Paginado()
    {
        var anonimo = _factory.CreateClient();
        var sinToken = await anonimo.GetAsync("/api/v1/animal");
        Assert.Equal(HttpStatusCode.Unauthorized, sinToken.StatusCode);

        var client = await ClienteAutenticadoAsync();
        var conToken = await client.GetAsync("/api/v1/animal?page=1&per_page=15");

        Assert.Equal(HttpStatusCode.OK, conToken.StatusCode);

        var pagina = await conToken.Content.ReadFromJsonAsync<PagedResult<AnimalResponseDto>>();
        Assert.NotNull(pagina);
        Assert.Equal(1, pagina!.Meta.CurrentPage);
        Assert.True(pagina.Meta.Total >= 2);
    }

    /// <summary>Prueba 2: la creacion valida devuelve 201 con Location; la invalida devuelve 422 por campo.</summary>
    [Fact]
    public async Task Creacion_Valida_Devuelve201ConLocation_Invalida_Devuelve422()
    {
        var client = await ClienteAutenticadoAsync();

        var valido = await client.PostAsJsonAsync("/api/v1/animal", new
        {
            numeroArete = $"CR-{Guid.NewGuid().ToString()[..8]}-LIB",
            raza = "Brahman",
            sexo = "Hembra",
            fechaNacimiento = "2024-01-10",
            estado = "Activo",
            potreroId = 1
        });

        Assert.Equal(HttpStatusCode.Created, valido.StatusCode);
        Assert.NotNull(valido.Headers.Location);

        var invalido = await client.PostAsJsonAsync("/api/v1/animal", new
        {
            numeroArete = "",
            raza = "",
            sexo = "Indefinido",
            fechaNacimiento = "2024-01-10",
            estado = "Volando",
            potreroId = 0
        });

        Assert.Equal(HttpStatusCode.UnprocessableEntity, invalido.StatusCode);

        var problema = await invalido.Content.ReadFromJsonAsync<ValidationProblemResponse>();
        Assert.NotNull(problema);
        Assert.NotEmpty(problema!.Errors);
    }

    /// <summary>Prueba 3: detalle y eliminacion devuelven 404 cuando el recurso no existe; el borrado valido devuelve 204.</summary>
    [Fact]
    public async Task Detalle_Y_Eliminacion_RespetanCodigosDelContrato()
    {
        var client = await ClienteAutenticadoAsync();

        var inexistente = await client.GetAsync("/api/v1/animal/999999");
        Assert.Equal(HttpStatusCode.NotFound, inexistente.StatusCode);

        var creado = await client.PostAsJsonAsync("/api/v1/animal", new
        {
            numeroArete = $"CR-{Guid.NewGuid().ToString()[..8]}-LIB",
            raza = "Nelore",
            sexo = "Macho",
            fechaNacimiento = "2023-03-01",
            estado = "Activo",
            potreroId = 1
        });

        var animal = await creado.Content.ReadFromJsonAsync<AnimalResponseDto>();

        var detalle = await client.GetAsync($"/api/v1/animal/{animal!.Id}");
        Assert.Equal(HttpStatusCode.OK, detalle.StatusCode);

        var borrado = await client.DeleteAsync($"/api/v1/animal/{animal.Id}");
        Assert.Equal(HttpStatusCode.NoContent, borrado.StatusCode);

        var borradoOtraVez = await client.DeleteAsync($"/api/v1/animal/{animal.Id}");
        Assert.Equal(HttpStatusCode.NotFound, borradoOtraVez.StatusCode);
    }

    private class ValidationProblemResponse
    {
        public Dictionary<string, string[]> Errors { get; set; } = new();
    }
}
